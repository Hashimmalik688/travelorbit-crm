# Plan: Multi-Hotel Rooms, Transfers Step & Dynamic Hotel Insertion

## Overview
Three connected changes to the booking wizard:
1. **Dynamic "Add Hotel" button** on Flight step (holiday/umrah) — hotel step is no longer auto-shown
2. **Multi-room hotel support** — each hotel has N rooms, each room has type, occupants, meal basis
3. **Transfers step** — pickup/dropoff management for transfers booking type

---

## Part 1: Database

### 1a. Alter `booking_hotels` — drop room_type & occupants
These move to the new rooms table.
**File:** New migration `database/migrations/YYYY_MM_DD_HHMMSS_restructure_hotels_for_rooms.php`
- `Schema::table('booking_hotels', fn)` — dropColumn `room_type`, dropColumn `occupants`

### 1b. Create `booking_hotel_rooms` table
**File:** New migration (same file as above, or a second migration)
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigIncrements | |
| `booking_hotel_id` | foreignId → booking_hotels | cascadeOnDelete |
| `room_number` | unsignedInteger, default 1 | e.g. 1, 2, 3 |
| `room_type` | string, nullable | "Double", "Single", "Suite" |
| `occupants` | unsignedInteger, default 1 | |
| `meal_basis` | enum: `room_only`, `breakfast`, `half_board`, `full_board`, `all_inclusive` | default `room_only` |
| `timestamps` | | |

### 1c. Create `booking_transfers` table
**File:** New migration `database/migrations/YYYY_MM_DD_HHMMSS_create_booking_transfers_table.php`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigIncrements | |
| `booking_id` | foreignId → bookings | cascadeOnDelete |
| `type` | enum: `pickup`, `dropoff` | |
| `location` | string | pickup/dropoff location |
| `date_time` | datetime, nullable | |
| `flight_number` | string, nullable | |
| `route` | text, nullable | free-text route description |
| `timestamps` | | |

---

## Part 2: Models

### 2a. Update `BookingHotel` (`app/Models/BookingHotel.php`)
- Remove `room_type` and `occupants` from `$fillable`
- Add `rooms()` relationship: `$this->hasMany(BookingHotelRoom::class)`

### 2b. New model `BookingHotelRoom` (`app/Models/BookingHotelRoom.php`)
- Fillable: `booking_hotel_id`, `room_number`, `room_type`, `occupants`, `meal_basis`
- BelongsTo: `BookingHotel`

### 2c. New model `BookingTransfer` (`app/Models/BookingTransfer.php`)
- Fillable: `booking_id`, `type`, `location`, `date_time`, `flight_number`, `route`
- BelongsTo: `Booking`
- Casts: `date_time` → datetime

### 2d. Update `Booking` model (`app/Models/Booking.php`)
- Add `transfers()` relationship: `$this->hasMany(BookingTransfer::class)`

---

## Part 3: Livewire — `CreateBooking.php`

### 3a. Replace single-hotel properties with `$hotels` array
Remove these flat properties (lines 89-98):
```
$hotel_name, $hotel_city, $hotel_room_type, $hotel_status,
$hotel_check_in, $hotel_check_out, $hotel_occupants,
$hotel_actual_cost, $hotel_selling_price
```
Replace with:
```php
public $hotels = [];
```
Default structure per hotel: hotel_name, city, status, check_in, check_out, actual_cost, selling_price, rooms[] (each with room_type, occupants, meal_basis).

### 3b. Step labels — remove auto-hotel for holiday
In `getStepLabelsProperty()`: holiday no longer auto-includes hotel step. Hotel step appears for `hotel` type OR dynamically when `$hotels` is non-empty.
Add transfers step for `transfers` booking type.

### 3c. Dynamic hotel step insertion
`addHotel()` method appends to `$hotels`, calls `recalcSteps()` and `updateCurrentStepId()`.
`removeHotel()` removes hotel, and if hotels become empty, recalc steps and move off hotel step.

### 3d. Room auto-generation from count
`updatedHotels` hook watches `hotels.N.number_of_rooms` — pads or trims `rooms` array to match count.

### 3e. Transfer properties & methods
`$transferPickups = []`, `$transferDropoffs = []` with add/remove methods.

### 3f. Updated save logic
Loop hotels and rooms on save. Create BookingTransfer records.

### 3g. Updated computed properties
Sum across all hotels for totalCostPrice, totalSoldPrice, hotelMargin.

### 3h. Remove old updated hooks (`updatedHotelActualCost`, `updatedHotelSellingPrice`)

---

## Part 4: Blade — `create-booking.blade.php`

### 4a. "Add Hotel" button on Flight step (holiday/umrah only)
### 4b. Rewrite Hotel step — iterate $hotels, each with rooms array
- Check In / Check Out side by side
- Number of Rooms input auto-generates room blocks
- Each room has: Room Type, Occupants, Meal Basis dropdown
- "Add Another Hotel" button at bottom of step
### 4c. New Transfers step — Pickup & Dropoff cards with Add/Remove buttons
### 4d. Payment step totals — sum all hotels instead of single hotel

---

## Part 5: Edit & Show views
### 5a. `booking-edit.blade.php` — iterate hotels, show rooms, add transfers
### 5b. `booking-show.blade.php` — iterate hotels with rooms, show transfers
### 5c. `BookingEdit.php` — hydrate hotels with rooms, transfer properties

---

## Part 6: Verification
1. `php artisan migrate`
2. Holiday booking → no auto hotel → click Add Hotel → step appears
3. Set rooms to 3 → 3 room blocks, set back to 1 → 1 block
4. Check In/Out side by side
5. Add Another Hotel → second hotel card
6. Save → DB records correct
7. Transfers booking → step visible → add pickups/dropoffs → save

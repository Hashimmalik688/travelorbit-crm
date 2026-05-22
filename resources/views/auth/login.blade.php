@extends('layouts.blankLayout')

@section('title', 'Sign In')

@section('page-style')
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html, body { height: 100%; }

body {
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  -webkit-font-smoothing: antialiased;
}

/* ─────────────────────────────
   Root layout
───────────────────────────── */
.auth-wrap {
  display: flex;
  min-height: 100vh;
}

/* ─────────────────────────────
   Left — photo panel
───────────────────────────── */
.auth-photo {
  flex: 1;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  padding: 44px 52px;

  /* High-quality Unsplash travel photo */
  background-image: url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1400&q=85&auto=format&fit=crop');
  background-size: cover;
  background-position: center 40%;
}

/* Dark overlay for readability */
.auth-photo::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to bottom,
    rgba(5, 5, 15, 0.62) 0%,
    rgba(5, 5, 15, 0.45) 50%,
    rgba(5, 5, 15, 0.72) 100%
  );
  pointer-events: none;
}

.auth-photo > * { position: relative; z-index: 1; }

/* Logo */
.auth-logo {
  display: block;
}

.auth-logo img {
  height: 100px;
  width: auto;
  filter: brightness(0) invert(1);
  opacity: 0.95;
  display: block;
}

/* Push headline to vertical center */
.auth-photo-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding-bottom: 48px;
}

.auth-eyebrow {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.95rem;
  font-weight: 600;
  letter-spacing: 0.01em;
  color: rgba(255,255,255,0.55);
  margin-bottom: 20px;
}

.auth-headline {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: clamp(2rem, 3.5vw, 2.8rem);
  font-weight: 800;
  color: #fff;
  line-height: 1.12;
  letter-spacing: -0.03em;
  margin-bottom: 20px;
}

.auth-desc {
  font-size: 0.95rem;
  color: rgba(255,255,255,0.65);
  line-height: 1.65;
  max-width: 400px;
}

/* Photo credit */
.auth-credit {
  font-size: 0.68rem;
  color: rgba(255,255,255,0.25);
  margin-top: auto;
  padding-top: 16px;
}

/* ─────────────────────────────
   Right — form panel
───────────────────────────── */
.auth-form-wrap {
  width: 460px;
  flex-shrink: 0;
  background: #fff;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 56px 48px;
  border-left: 1px solid #E8EAF0;
}

.auth-form-title {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 1.65rem;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.025em;
  line-height: 1.2;
  margin-bottom: 6px;
}

.auth-form-sub {
  font-size: 0.875rem;
  color: #9CA3AF;
  margin-bottom: 36px;
}

/* Fields */
.field {
  margin-bottom: 20px;
}

.field-lbl {
  display: block;
  font-size: 0.73rem;
  font-weight: 700;
  color: #6B7280;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 8px;
}

.field-ctrl {
  display: block;
  width: 100%;
  padding: 11px 14px;
  background: #FAF8F5;
  border: 1.5px solid #E8E2D8;
  border-radius: 10px;
  font-family: inherit;
  font-size: 0.9rem;
  color: #20242B;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
}

.field-ctrl:focus {
  border-color: #332E9E;
  box-shadow: 0 0 0 3px rgba(51,46,158,0.12);
  background: #fff;
}

.field-ctrl::placeholder { color: #D1D5DB; }
.field-ctrl.is-invalid   { border-color: #EF4444; }

/* Ripple button — uiverse.io inspired */
.ripple {
  position: relative;
  overflow: hidden;
}
.ripple::after {
  content: '';
  position: absolute;
  top: 50%; left: 50%;
  width: 0; height: 0;
  border-radius: 50%;
  background: rgba(255,255,255,0.25);
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}
.ripple:active::after {
  width: 300px; height: 300px;
}

.field-pw-wrap { position: relative; }
.field-pw-wrap .field-ctrl { padding-right: 44px; }

.toggle-pw {
  position: absolute;
  right: 12px; top: 50%;
  transform: translateY(-50%);
  background: none; border: none;
  color: #9CA3AF; cursor: pointer;
  font-size: 1.1rem; padding: 4px;
  line-height: 1;
  transition: color 0.12s;
}
.toggle-pw:hover { color: #332E9E; }

/* Options */
.auth-opts {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin: 4px 0 28px;
}

.auth-check-lbl {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.855rem;
  color: #6B7280;
  font-weight: 500;
  cursor: pointer;
  user-select: none;
}

.auth-check-lbl input[type="checkbox"] {
  width: 15px; height: 15px;
  border-radius: 4px;
  accent-color: #FF6B35;
  cursor: pointer;
}

.auth-forgot {
  font-size: 0.83rem;
  font-weight: 600;
  color: #332E9E;
  text-decoration: none;
  transition: color 0.12s;
}
.auth-forgot:hover { color: #FF6B35; }

/* Submit */
.btn-signin {
  display: block;
  width: 100%;
  padding: 12px;
  background: #332E9E;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-family: inherit;
  font-size: 0.9rem;
  font-weight: 700;
  letter-spacing: 0.01em;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(51,46,158,0.22);
  transition: background 0.15s, box-shadow 0.15s, transform 0.1s;
}
.btn-signin:hover {
  background: #231E7A;
  box-shadow: 0 4px 16px rgba(51,46,158,0.35);
  transform: translateY(-1px);
}
.btn-signin:active {
  background: #1B1664;
  transform: none;
  box-shadow: 0 1px 3px rgba(51,46,158,0.18);
}

/* Error */
.auth-err {
  background: #FEF2F2;
  border-left: 3px solid #EF4444;
  border-radius: 8px;
  padding: 10px 14px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  color: #991B1B;
}

/* Footer */
.auth-form-footer {
  margin-top: 36px;
  font-size: 0.73rem;
  color: #D1D5DB;
  text-align: center;
}

/* ─────────────────────────────
   Mobile
───────────────────────────── */
@media (max-width: 800px) {
  .auth-photo { display: none; }
  .auth-form-wrap { width: 100%; border-left: none; padding: 40px 24px; }
}
</style>
@endsection

@section('content')
<div class="auth-wrap">

  {{-- Photo panel --}}
  <div class="auth-photo">
    <a href="/" class="auth-logo">
      <img src="{{ asset('logo.png') }}" alt="Travel Orbit">
    </a>

    <div class="auth-photo-body">
      <p class="auth-eyebrow">Travel Orbit MIS</p>
      <h1 class="auth-headline">
        Every booking.<br>Every agent.<br>One system.
      </h1>
      <p class="auth-desc">
        Manage bookings, track payments, monitor agent performance
        and report on your business — all in one place.
      </p>
    </div>

  </div>

  {{-- Form panel --}}
  <div class="auth-form-wrap">
    <div class="auth-form-title">Welcome back</div>
    <div class="auth-form-sub">Sign in to your account</div>

    <form method="POST" action="{{ route('login') }}">
      @csrf

      @if ($errors->any())
      <div class="auth-err">
        <i class="bx bx-error-circle" style="font-size:1rem;flex-shrink:0;"></i>
        {{ $errors->first() }}
      </div>
      @endif

      <div class="field">
        <label class="field-lbl" for="email">Email</label>
        <input
          type="email" id="email" name="email"
          class="field-ctrl {{ $errors->has('email') ? 'is-invalid' : '' }}"
          value="{{ old('email') }}"
          placeholder="you@travelorbit.co.uk"
          autocomplete="email" autofocus>
      </div>

      <div class="field">
        <label class="field-lbl" for="password">Password</label>
        <div class="field-pw-wrap">
          <input
            type="password" id="password" name="password"
            class="field-ctrl {{ $errors->has('password') ? 'is-invalid' : '' }}"
            placeholder="••••••••••"
            autocomplete="current-password">
          <button type="button" class="toggle-pw" onclick="togglePw()" aria-label="Show password">
            <i class="bx bx-hide" id="pwIcon"></i>
          </button>
        </div>
      </div>

      <div class="auth-opts">
        <label class="auth-check-lbl">
          <input type="checkbox" name="remember">
          Keep me signed in
        </label>
      </div>

      <button type="submit" class="btn-signin">Sign in</button>
    </form>

    <div class="auth-form-footer">
      &copy; {{ date('Y') }} Travel Orbit Ltd. &nbsp;·&nbsp; Internal use only.
    </div>
  </div>

</div>

<script>
function togglePw() {
  const pw   = document.getElementById('password');
  const icon = document.getElementById('pwIcon');
  pw.type    = pw.type === 'password' ? 'text' : 'password';
  icon.className = pw.type === 'text' ? 'bx bx-show' : 'bx bx-hide';
}
</script>
@endsection

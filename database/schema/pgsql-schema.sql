--
-- PostgreSQL database dump
--

\restrict qVerofprIRGwIP05U3hjnB9u0ih6iAC8u4GY9wQ7kLar8nTs9IGSRLf8epuaTuK

-- Dumped from database version 16.14 (Ubuntu 16.14-0ubuntu0.24.04.1)
-- Dumped by pg_dump version 16.14 (Ubuntu 16.14-0ubuntu0.24.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.audit_logs (
    id bigint NOT NULL,
    user_id bigint,
    user_email character varying(255),
    action character varying(100) NOT NULL,
    model character varying(100),
    model_id bigint,
    ip_address character varying(45),
    user_agent text,
    changes jsonb,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: audit_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.audit_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: audit_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.audit_logs_id_seq OWNED BY public.audit_logs.id;


--
-- Name: booking_activity_log; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.booking_activity_log (
    id bigint NOT NULL,
    booking_id bigint NOT NULL,
    user_id bigint NOT NULL,
    action character varying(255) NOT NULL,
    comment text NOT NULL,
    details json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: booking_activity_log_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.booking_activity_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: booking_activity_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.booking_activity_log_id_seq OWNED BY public.booking_activity_log.id;


--
-- Name: booking_comments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.booking_comments (
    id bigint NOT NULL,
    booking_id bigint NOT NULL,
    user_id bigint NOT NULL,
    comment text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    action character varying(255),
    is_mandatory boolean DEFAULT false NOT NULL,
    agent_name character varying(255),
    avatar_url character varying(1000)
);


--
-- Name: booking_comments_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.booking_comments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: booking_comments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.booking_comments_id_seq OWNED BY public.booking_comments.id;


--
-- Name: booking_documents; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.booking_documents (
    id bigint NOT NULL,
    booking_id bigint NOT NULL,
    uploaded_by bigint NOT NULL,
    file_name character varying(255) NOT NULL,
    file_path character varying(255) NOT NULL,
    file_type character varying(255) NOT NULL,
    document_type character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT booking_documents_document_type_check CHECK (((document_type)::text = ANY ((ARRAY['passport'::character varying, 'cnic'::character varying, 'other'::character varying, 'visa'::character varying, 'itinerary'::character varying, 'invoice'::character varying])::text[])))
);


--
-- Name: booking_documents_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.booking_documents_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: booking_documents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.booking_documents_id_seq OWNED BY public.booking_documents.id;


--
-- Name: booking_flight_costs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.booking_flight_costs (
    id bigint NOT NULL,
    booking_id bigint NOT NULL,
    cost_type character varying(255) NOT NULL,
    cost numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    quantity integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    sold_price numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    CONSTRAINT booking_flight_costs_cost_type_check CHECK (((cost_type)::text = ANY ((ARRAY['adult'::character varying, 'child'::character varying, 'infant'::character varying, 'gbe'::character varying])::text[])))
);


--
-- Name: booking_flight_costs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.booking_flight_costs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: booking_flight_costs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.booking_flight_costs_id_seq OWNED BY public.booking_flight_costs.id;


--
-- Name: booking_flight_details; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.booking_flight_details (
    id bigint NOT NULL,
    booking_id bigint NOT NULL,
    pnr text,
    airline character varying(2),
    vendor character varying(255),
    gds character varying(255),
    ticket_issue_limit timestamp(0) without time zone,
    atol boolean DEFAULT false NOT NULL,
    safi boolean DEFAULT false NOT NULL,
    departure_airport character varying(255),
    arrival_airport character varying(255),
    departure_date date,
    return_date date,
    selling_price numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    folder_number character varying(255),
    locator character varying(255),
    airline_locator character varying(255),
    type_issuer character varying(255),
    reservation_status character varying(255),
    cabin character varying(20),
    cost numeric(10,2),
    sold numeric(10,2),
    passenger_costs json,
    flight_type character varying(255) DEFAULT 'return'::character varying NOT NULL
);


--
-- Name: booking_flight_details_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.booking_flight_details_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: booking_flight_details_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.booking_flight_details_id_seq OWNED BY public.booking_flight_details.id;


--
-- Name: booking_hotel_rooms; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.booking_hotel_rooms (
    id bigint NOT NULL,
    booking_hotel_id bigint NOT NULL,
    room_number integer DEFAULT 1 NOT NULL,
    room_type character varying(255),
    occupants integer DEFAULT 1 NOT NULL,
    meal_basis character varying(255) DEFAULT 'room_only'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT booking_hotel_rooms_meal_basis_check CHECK (((meal_basis)::text = ANY ((ARRAY['room_only'::character varying, 'breakfast'::character varying, 'half_board'::character varying, 'full_board'::character varying, 'all_inclusive'::character varying])::text[])))
);


--
-- Name: booking_hotel_rooms_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.booking_hotel_rooms_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: booking_hotel_rooms_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.booking_hotel_rooms_id_seq OWNED BY public.booking_hotel_rooms.id;


--
-- Name: booking_hotels; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.booking_hotels (
    id bigint NOT NULL,
    booking_id bigint NOT NULL,
    hotel_name character varying(255) NOT NULL,
    city character varying(255),
    booking_status character varying(255) DEFAULT 'confirmed'::character varying NOT NULL,
    check_in date,
    check_out date,
    actual_cost numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    selling_price numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT booking_hotels_booking_status_check CHECK (((booking_status)::text = ANY ((ARRAY['confirmed'::character varying, 'on_holding'::character varying, 'cancelled'::character varying])::text[])))
);


--
-- Name: booking_hotels_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.booking_hotels_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: booking_hotels_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.booking_hotels_id_seq OWNED BY public.booking_hotels.id;


--
-- Name: booking_passengers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.booking_passengers (
    id bigint NOT NULL,
    booking_id bigint NOT NULL,
    full_name character varying(255) NOT NULL,
    passport_number character varying(255),
    national_id_number character varying(255),
    nationality character varying(255),
    date_of_birth date,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    title character varying(255),
    passport_country_code character varying(3),
    passport_issuing_country character varying(255),
    passenger_type character varying(255) DEFAULT 'adult'::character varying NOT NULL,
    first_name character varying(255),
    last_name character varying(255),
    ticket_number character varying(255),
    passenger_status_label character varying(255),
    frequent_flyer_number character varying(255),
    e_ticket_number character varying(255),
    ptc character varying(255),
    contact_number character varying(255),
    cost_per_pax numeric(10,2),
    sold_per_pax numeric(10,2),
    CONSTRAINT booking_passengers_passenger_type_check CHECK (((passenger_type)::text = ANY ((ARRAY['adult'::character varying, 'youth'::character varying, 'child'::character varying, 'infant'::character varying, 'gbe'::character varying])::text[]))),
    CONSTRAINT booking_passengers_title_check CHECK (((title)::text = ANY ((ARRAY['Mr.'::character varying, 'Ms.'::character varying, 'Mrs.'::character varying, 'Mstr'::character varying, 'Miss'::character varying, 'Dr.'::character varying, 'mr'::character varying, 'mrs'::character varying, 'ms'::character varying, 'dr'::character varying])::text[])))
);


--
-- Name: booking_passengers_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.booking_passengers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: booking_passengers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.booking_passengers_id_seq OWNED BY public.booking_passengers.id;


--
-- Name: booking_payment_history; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.booking_payment_history (
    id bigint NOT NULL,
    booking_id bigint NOT NULL,
    user_id bigint NOT NULL,
    payment_date date NOT NULL,
    payment_method character varying(255),
    amount numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    receipt_number character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    approved_by bigint,
    payment_details json
);


--
-- Name: booking_payment_history_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.booking_payment_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: booking_payment_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.booking_payment_history_id_seq OWNED BY public.booking_payment_history.id;


--
-- Name: booking_payments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.booking_payments (
    id bigint NOT NULL,
    booking_id bigint NOT NULL,
    booking_plan character varying(255) NOT NULL,
    amount_paid numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    total_amount numeric(10,2) NOT NULL,
    balance_remaining numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    due_date date,
    installment_period character varying(255) DEFAULT 'none'::character varying NOT NULL,
    deposit_amount numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    is_deposit_nonrefundable boolean DEFAULT true NOT NULL,
    payment_mode character varying(50) DEFAULT 'none'::character varying NOT NULL,
    invoice_generated boolean DEFAULT false NOT NULL,
    invoice_generated_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    payment_mode_2 character varying(255),
    installment_first_amount numeric(10,2),
    debit_card_change boolean DEFAULT false NOT NULL,
    cc_charges numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    margin_without_cc numeric(10,2),
    total_margin_amount numeric(10,2),
    CONSTRAINT booking_payments_installment_period_check CHECK (((installment_period)::text = ANY ((ARRAY['none'::character varying, '30_days'::character varying, '2_months'::character varying])::text[])))
);


--
-- Name: booking_payments_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.booking_payments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: booking_payments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.booking_payments_id_seq OWNED BY public.booking_payments.id;


--
-- Name: booking_transfers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.booking_transfers (
    id bigint NOT NULL,
    booking_id bigint NOT NULL,
    type character varying(255) NOT NULL,
    location character varying(255) NOT NULL,
    date_time timestamp(0) without time zone,
    flight_number character varying(255),
    route text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    vehicle_type character varying(255),
    supplier character varying(255),
    actual_cost numeric(10,2),
    selling_price numeric(10,2),
    status character varying(255) DEFAULT 'confirmed'::character varying NOT NULL,
    notes text,
    CONSTRAINT booking_transfers_type_check CHECK (((type)::text = ANY ((ARRAY['pickup'::character varying, 'dropoff'::character varying])::text[])))
);


--
-- Name: booking_transfers_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.booking_transfers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: booking_transfers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.booking_transfers_id_seq OWNED BY public.booking_transfers.id;


--
-- Name: booking_visas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.booking_visas (
    id bigint NOT NULL,
    booking_id bigint NOT NULL,
    passenger_name character varying(255),
    visa_type character varying(255) DEFAULT 'umrah'::character varying NOT NULL,
    visa_reference character varying(255),
    visa_number character varying(255),
    application_date date,
    issue_date date,
    expiry_date date,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    actual_cost numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    selling_price numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: booking_visas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.booking_visas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: booking_visas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.booking_visas_id_seq OWNED BY public.booking_visas.id;


--
-- Name: bookings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.bookings (
    id bigint NOT NULL,
    customer_id bigint NOT NULL,
    user_id bigint NOT NULL,
    booking_type character varying(255) NOT NULL,
    passenger_count smallint DEFAULT '1'::smallint NOT NULL,
    booking_status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    booking_number integer NOT NULL,
    lead_source character varying(255),
    booker_email character varying(255),
    booker_whatsapp character varying(255),
    issuance_requested_at timestamp(0) without time zone,
    refund_requested_at timestamp(0) without time zone,
    refund_reason text,
    referral_name character varying(255),
    booker_address text,
    booker_postcode character varying(255),
    booker_country character varying(255) DEFAULT 'UK'::character varying NOT NULL,
    booker_title character varying(255),
    booker_first_name character varying(255),
    booker_last_name character varying(255),
    booker_mobile character varying(255),
    booker_landline character varying(255),
    lead_nature character varying(255),
    is_returning_or_referral boolean DEFAULT false NOT NULL,
    old_booking_reference character varying(255),
    last_payment_date date,
    last_issue_date date,
    activity_log json,
    previous_booking_type character varying(255),
    invoiced_at timestamp without time zone,
    ticket_processed_at timestamp without time zone,
    issuance_queued_at timestamp without time zone,
    locked_by_role character varying(50),
    excursion_data jsonb,
    CONSTRAINT bookings_booking_status_check CHECK (((booking_status)::text = ANY ((ARRAY['pending'::character varying, 'confirmed'::character varying, 'issuance_queue'::character varying, 'ticket_in_process'::character varying, 'invoiced'::character varying, 'issued'::character varying, 'issued_payment_awaiting'::character varying, 'issued_payment_plan'::character varying, 'payment_charge_request'::character varying, 'cancelled'::character varying, 'refund_queue'::character varying])::text[]))),
    CONSTRAINT bookings_booking_type_check CHECK (((booking_type)::text = ANY ((ARRAY['flight'::character varying, 'hotel'::character varying, 'umrah'::character varying, 'holiday'::character varying, 'transfers'::character varying, 'ancillary_services'::character varying, 'excursion'::character varying, 'visa'::character varying])::text[]))),
    CONSTRAINT bookings_lead_source_check CHECK (((lead_source)::text = ANY ((ARRAY['to_returning'::character varying, 'to_referral'::character varying, 'referral_client'::character varying, 'returning_client'::character varying, 'fb'::character varying, 'wa'::character varying, 'email'::character varying, 'diaspora_group'::character varying, 'instagram'::character varying, 'tiktok'::character varying, 'website'::character varying, 'google'::character varying])::text[])))
);


--
-- Name: bookings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.bookings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: bookings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.bookings_id_seq OWNED BY public.bookings.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: customers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.customers (
    id bigint NOT NULL,
    full_name character varying(255) NOT NULL,
    phone character varying(255) NOT NULL,
    whatsapp character varying(255),
    email character varying(255),
    nationality character varying(255),
    notes text,
    created_by bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


--
-- Name: customers_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.customers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: customers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.customers_id_seq OWNED BY public.customers.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: payment_schedule; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.payment_schedule (
    id bigint NOT NULL,
    booking_id bigint NOT NULL,
    installment_number smallint NOT NULL,
    amount numeric(10,2) NOT NULL,
    due_date date NOT NULL,
    paid_date date,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    payment_mode character varying(255),
    notes text,
    recorded_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT payment_schedule_payment_mode_check CHECK (((payment_mode)::text = ANY ((ARRAY['cash'::character varying, 'bank_transfer'::character varying, 'stripe'::character varying, 'klarna'::character varying, 'card'::character varying])::text[]))),
    CONSTRAINT payment_schedule_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'paid'::character varying, 'overdue'::character varying])::text[])))
);


--
-- Name: payment_schedule_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.payment_schedule_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: payment_schedule_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.payment_schedule_id_seq OWNED BY public.payment_schedule.id;


--
-- Name: refunds; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.refunds (
    id bigint NOT NULL,
    booking_id bigint NOT NULL,
    requested_by bigint NOT NULL,
    processed_by bigint,
    refund_amount numeric(10,2) NOT NULL,
    reason text NOT NULL,
    status character varying(255) DEFAULT 'requested'::character varying NOT NULL,
    refund_method character varying(255),
    requested_at timestamp(0) without time zone,
    reviewed_at timestamp(0) without time zone,
    processed_at timestamp(0) without time zone,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT refunds_refund_method_check CHECK (((refund_method)::text = ANY ((ARRAY['cash'::character varying, 'bank_transfer'::character varying, 'stripe'::character varying, 'klarna'::character varying])::text[]))),
    CONSTRAINT refunds_status_check CHECK (((status)::text = ANY ((ARRAY['requested'::character varying, 'under_review'::character varying, 'approved'::character varying, 'rejected'::character varying, 'processed'::character varying])::text[])))
);


--
-- Name: refunds_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.refunds_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: refunds_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.refunds_id_seq OWNED BY public.refunds.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.settings (
    id bigint NOT NULL,
    key character varying(255) NOT NULL,
    value json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: settings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.settings_id_seq OWNED BY public.settings.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    role character varying(255) DEFAULT 'agent'::character varying NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    profile_photo_path character varying(255),
    last_login_at timestamp(0) without time zone,
    last_login_ip character varying(45),
    status character varying(20) DEFAULT 'active'::character varying NOT NULL,
    password_plaintext character varying(255),
    CONSTRAINT users_role_check CHECK (((role)::text = ANY (ARRAY['admin'::text, 'manager'::text, 'agent'::text, 'accounts'::text, 'issuance'::text, 'operations'::text])))
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: audit_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN id SET DEFAULT nextval('public.audit_logs_id_seq'::regclass);


--
-- Name: booking_activity_log id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_activity_log ALTER COLUMN id SET DEFAULT nextval('public.booking_activity_log_id_seq'::regclass);


--
-- Name: booking_comments id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_comments ALTER COLUMN id SET DEFAULT nextval('public.booking_comments_id_seq'::regclass);


--
-- Name: booking_documents id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_documents ALTER COLUMN id SET DEFAULT nextval('public.booking_documents_id_seq'::regclass);


--
-- Name: booking_flight_costs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_flight_costs ALTER COLUMN id SET DEFAULT nextval('public.booking_flight_costs_id_seq'::regclass);


--
-- Name: booking_flight_details id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_flight_details ALTER COLUMN id SET DEFAULT nextval('public.booking_flight_details_id_seq'::regclass);


--
-- Name: booking_hotel_rooms id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_hotel_rooms ALTER COLUMN id SET DEFAULT nextval('public.booking_hotel_rooms_id_seq'::regclass);


--
-- Name: booking_hotels id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_hotels ALTER COLUMN id SET DEFAULT nextval('public.booking_hotels_id_seq'::regclass);


--
-- Name: booking_passengers id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_passengers ALTER COLUMN id SET DEFAULT nextval('public.booking_passengers_id_seq'::regclass);


--
-- Name: booking_payment_history id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_payment_history ALTER COLUMN id SET DEFAULT nextval('public.booking_payment_history_id_seq'::regclass);


--
-- Name: booking_payments id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_payments ALTER COLUMN id SET DEFAULT nextval('public.booking_payments_id_seq'::regclass);


--
-- Name: booking_transfers id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_transfers ALTER COLUMN id SET DEFAULT nextval('public.booking_transfers_id_seq'::regclass);


--
-- Name: booking_visas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_visas ALTER COLUMN id SET DEFAULT nextval('public.booking_visas_id_seq'::regclass);


--
-- Name: bookings id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookings ALTER COLUMN id SET DEFAULT nextval('public.bookings_id_seq'::regclass);


--
-- Name: customers id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customers ALTER COLUMN id SET DEFAULT nextval('public.customers_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: payment_schedule id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payment_schedule ALTER COLUMN id SET DEFAULT nextval('public.payment_schedule_id_seq'::regclass);


--
-- Name: refunds id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.refunds ALTER COLUMN id SET DEFAULT nextval('public.refunds_id_seq'::regclass);


--
-- Name: settings id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.settings ALTER COLUMN id SET DEFAULT nextval('public.settings_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- Name: booking_activity_log booking_activity_log_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_activity_log
    ADD CONSTRAINT booking_activity_log_pkey PRIMARY KEY (id);


--
-- Name: booking_comments booking_comments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_comments
    ADD CONSTRAINT booking_comments_pkey PRIMARY KEY (id);


--
-- Name: booking_documents booking_documents_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_documents
    ADD CONSTRAINT booking_documents_pkey PRIMARY KEY (id);


--
-- Name: booking_flight_costs booking_flight_costs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_flight_costs
    ADD CONSTRAINT booking_flight_costs_pkey PRIMARY KEY (id);


--
-- Name: booking_flight_details booking_flight_details_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_flight_details
    ADD CONSTRAINT booking_flight_details_pkey PRIMARY KEY (id);


--
-- Name: booking_hotel_rooms booking_hotel_rooms_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_hotel_rooms
    ADD CONSTRAINT booking_hotel_rooms_pkey PRIMARY KEY (id);


--
-- Name: booking_hotels booking_hotels_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_hotels
    ADD CONSTRAINT booking_hotels_pkey PRIMARY KEY (id);


--
-- Name: booking_passengers booking_passengers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_passengers
    ADD CONSTRAINT booking_passengers_pkey PRIMARY KEY (id);


--
-- Name: booking_payment_history booking_payment_history_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_payment_history
    ADD CONSTRAINT booking_payment_history_pkey PRIMARY KEY (id);


--
-- Name: booking_payments booking_payments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_payments
    ADD CONSTRAINT booking_payments_pkey PRIMARY KEY (id);


--
-- Name: booking_transfers booking_transfers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_transfers
    ADD CONSTRAINT booking_transfers_pkey PRIMARY KEY (id);


--
-- Name: booking_visas booking_visas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_visas
    ADD CONSTRAINT booking_visas_pkey PRIMARY KEY (id);


--
-- Name: bookings bookings_booking_number_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_booking_number_unique UNIQUE (booking_number);


--
-- Name: bookings bookings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: customers customers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: payment_schedule payment_schedule_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payment_schedule
    ADD CONSTRAINT payment_schedule_pkey PRIMARY KEY (id);


--
-- Name: refunds refunds_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.refunds
    ADD CONSTRAINT refunds_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: settings settings_key_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_key_unique UNIQUE (key);


--
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: audit_logs_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX audit_logs_created_at_index ON public.audit_logs USING btree (created_at);


--
-- Name: audit_logs_model_model_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX audit_logs_model_model_id_index ON public.audit_logs USING btree (model, model_id);


--
-- Name: audit_logs_user_id_action_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX audit_logs_user_id_action_index ON public.audit_logs USING btree (user_id, action);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: booking_activity_log booking_activity_log_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_activity_log
    ADD CONSTRAINT booking_activity_log_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.bookings(id) ON DELETE CASCADE;


--
-- Name: booking_activity_log booking_activity_log_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_activity_log
    ADD CONSTRAINT booking_activity_log_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: booking_comments booking_comments_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_comments
    ADD CONSTRAINT booking_comments_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.bookings(id) ON DELETE CASCADE;


--
-- Name: booking_comments booking_comments_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_comments
    ADD CONSTRAINT booking_comments_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: booking_documents booking_documents_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_documents
    ADD CONSTRAINT booking_documents_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.bookings(id) ON DELETE CASCADE;


--
-- Name: booking_documents booking_documents_uploaded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_documents
    ADD CONSTRAINT booking_documents_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: booking_flight_costs booking_flight_costs_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_flight_costs
    ADD CONSTRAINT booking_flight_costs_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.bookings(id) ON DELETE CASCADE;


--
-- Name: booking_flight_details booking_flight_details_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_flight_details
    ADD CONSTRAINT booking_flight_details_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.bookings(id) ON DELETE CASCADE;


--
-- Name: booking_hotel_rooms booking_hotel_rooms_booking_hotel_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_hotel_rooms
    ADD CONSTRAINT booking_hotel_rooms_booking_hotel_id_foreign FOREIGN KEY (booking_hotel_id) REFERENCES public.booking_hotels(id) ON DELETE CASCADE;


--
-- Name: booking_hotels booking_hotels_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_hotels
    ADD CONSTRAINT booking_hotels_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.bookings(id) ON DELETE CASCADE;


--
-- Name: booking_passengers booking_passengers_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_passengers
    ADD CONSTRAINT booking_passengers_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.bookings(id) ON DELETE CASCADE;


--
-- Name: booking_payment_history booking_payment_history_approved_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_payment_history
    ADD CONSTRAINT booking_payment_history_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: booking_payment_history booking_payment_history_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_payment_history
    ADD CONSTRAINT booking_payment_history_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.bookings(id) ON DELETE CASCADE;


--
-- Name: booking_payment_history booking_payment_history_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_payment_history
    ADD CONSTRAINT booking_payment_history_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: booking_payments booking_payments_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_payments
    ADD CONSTRAINT booking_payments_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.bookings(id) ON DELETE CASCADE;


--
-- Name: booking_transfers booking_transfers_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_transfers
    ADD CONSTRAINT booking_transfers_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.bookings(id) ON DELETE CASCADE;


--
-- Name: booking_visas booking_visas_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.booking_visas
    ADD CONSTRAINT booking_visas_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.bookings(id) ON DELETE CASCADE;


--
-- Name: bookings bookings_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE CASCADE;


--
-- Name: bookings bookings_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: customers customers_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- Name: payment_schedule payment_schedule_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payment_schedule
    ADD CONSTRAINT payment_schedule_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.bookings(id) ON DELETE CASCADE;


--
-- Name: payment_schedule payment_schedule_recorded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payment_schedule
    ADD CONSTRAINT payment_schedule_recorded_by_foreign FOREIGN KEY (recorded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: refunds refunds_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.refunds
    ADD CONSTRAINT refunds_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.bookings(id) ON DELETE CASCADE;


--
-- Name: refunds refunds_processed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.refunds
    ADD CONSTRAINT refunds_processed_by_foreign FOREIGN KEY (processed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: refunds refunds_requested_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.refunds
    ADD CONSTRAINT refunds_requested_by_foreign FOREIGN KEY (requested_by) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict qVerofprIRGwIP05U3hjnB9u0ih6iAC8u4GY9wQ7kLar8nTs9IGSRLf8epuaTuK

--
-- PostgreSQL database dump
--

\restrict BxxGaQUsXmRdeVLqWp125irEpM7s7oHsZJMoCkcLOcyZuMd9vQL8ejuMD1ftJPb

-- Dumped from database version 16.14 (Ubuntu 16.14-0ubuntu0.24.04.1)
-- Dumped by pg_dump version 16.14 (Ubuntu 16.14-0ubuntu0.24.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_05_17_160654_add_role_is_active_to_users	2
5	2026_05_17_160654_create_customers_table	2
6	2026_05_17_160655_create_bookings_table	2
7	2026_05_17_162843_create_booking_comments_table	3
8	2026_05_17_162843_create_booking_documents_table	3
9	2026_05_17_162843_create_booking_passengers_table	3
10	2026_05_17_162843_create_booking_payments_table	3
11	2026_05_17_162843_modify_bookings_add_columns	4
12	2026_05_17_165940_add_more_columns_to_bookings	5
13	2026_05_17_165940_modify_booking_passengers_rename_and_add_columns	5
14	2026_05_17_191330_create_audit_logs_table	6
15	2026_05_17_191330_create_payment_schedule_table	6
16	2026_05_17_194739_alter_users_role_enum	7
17	2026_05_17_195242_create_refunds_table	8
18	2026_05_17_201124_add_profile_photo_to_users	9
19	2026_05_17_204539_restructure_bookings_and_passengers_for_multi_passenger	10
20	2026_05_17_211414_add_passenger_type_to_booking_passengers	11
21	2026_05_17_214441_rename_passengers_to_passenger_count_on_bookings	12
22	2026_05_19_000001_restructure_bookings_for_wizard	13
23	2026_05_19_000002_create_booking_flight_details_table	13
24	2026_05_19_000003_create_booking_flight_costs_table	13
25	2026_05_19_000004_create_booking_hotels_table	13
26	2026_05_19_000005_restructure_booking_passengers_for_wizard	13
27	2026_05_22_000001_booking_form_v2_changes	14
28	2026_05_25_000001_add_excursion_to_booking_type	15
29	2026_05_30_075055_add_cabin_to_booking_flight_details_table	16
30	2026_05_30_100000_add_contact_number_to_booking_passengers_table	17
31	2026_05_30_110000_add_pricing_to_booking_passengers_table	18
32	2026_05_31_000001_add_booking_workflow_states	19
33	2026_05_31_000002_create_audit_logs_table	20
34	2026_05_31_120000_update_payment_mode_enum	21
35	2026_06_01_000001_restructure_hotels_for_rooms	22
36	2026_06_01_000002_create_booking_transfers_table	22
37	2026_06_03_164223_add_status_to_booking_payment_history	23
39	2026_06_06_172436_add_cost_sold_to_booking_flight_details_table	24
40	2026_06_06_210556_add_passenger_costs_to_booking_flight_details	25
41	2026_06_06_213918_add_payment_details_to_booking_payment_history	26
42	2026_06_07_000001_create_booking_visas_table	27
43	2026_06_07_000002_add_excursion_data_to_bookings	27
44	2026_06_07_001440_drop_booking_ref_from_bookings_table	28
45	2026_06_07_003740_add_extra_fields_to_booking_transfers_table	29
46	2026_06_07_130531_add_snapshot_columns_to_booking_comments_table	30
47	2026_06_11_000001_add_visa_booking_type	31
48	2026_06_13_034800_create_settings_table	32
49	2026_06_17_093956_add_missing_booking_statuses_to_check_constraint	33
50	2026_06_18_000001_add_flight_type_to_booking_flight_details	34
51	2026_06_18_184441_fix_users_role_check_constraint	35
52	2026_06_22_140233_add_password_plaintext_to_users_table	36
\.


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 52, true);


--
-- PostgreSQL database dump complete
--

\unrestrict BxxGaQUsXmRdeVLqWp125irEpM7s7oHsZJMoCkcLOcyZuMd9vQL8ejuMD1ftJPb


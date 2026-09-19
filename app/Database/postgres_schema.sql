
--
-- PostgreSQL database dump
--


-- Dumped from database version 18.6 (Ubuntu 18.6-0ubuntu0.26.04.1)
-- Dumped by pg_dump version 18.6 (Ubuntu 18.6-0ubuntu0.26.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

ALTER TABLE IF EXISTS ONLY public.vehicles DROP CONSTRAINT IF EXISTS vehicles_default_route_id_foreign;
ALTER TABLE IF EXISTS ONLY public.user_routes DROP CONSTRAINT IF EXISTS user_routes_user_id_foreign;
ALTER TABLE IF EXISTS ONLY public.user_routes DROP CONSTRAINT IF EXISTS user_routes_route_id_foreign;
ALTER TABLE IF EXISTS ONLY public.routes DROP CONSTRAINT IF EXISTS routes_terminal_id_foreign;
ALTER TABLE IF EXISTS ONLY public.queue DROP CONSTRAINT IF EXISTS queue_vehicle_id_foreign;
ALTER TABLE IF EXISTS ONLY public.queue DROP CONSTRAINT IF EXISTS queue_route_id_foreign;
ALTER TABLE IF EXISTS ONLY public.fares DROP CONSTRAINT IF EXISTS fk_fares_route;
ALTER TABLE IF EXISTS ONLY public.fares DROP CONSTRAINT IF EXISTS fk_fares_discount;
ALTER TABLE IF EXISTS ONLY public.departure_rules DROP CONSTRAINT IF EXISTS fk_departure_rules_route;
ALTER TABLE IF EXISTS ONLY public.announcements DROP CONSTRAINT IF EXISTS fk_announcements_terminal;
ALTER TABLE IF EXISTS ONLY public.audit_logs DROP CONSTRAINT IF EXISTS audit_logs_user_id_foreign;
DROP INDEX IF EXISTS public.password_reset_tokens_username;
DROP INDEX IF EXISTS public.password_reset_tokens_token;
DROP INDEX IF EXISTS public.idx_user_routes_user;
DROP INDEX IF EXISTS public.idx_user_routes_route;
DROP INDEX IF EXISTS public.idx_routes_terminal_dest;
DROP INDEX IF EXISTS public.idx_routes_status;
DROP INDEX IF EXISTS public.idx_queue_vehicle_status;
DROP INDEX IF EXISTS public.idx_queue_status_position;
DROP INDEX IF EXISTS public.idx_queue_status_departure;
DROP INDEX IF EXISTS public.idx_queue_status_arrival;
DROP INDEX IF EXISTS public.idx_queue_status;
DROP INDEX IF EXISTS public.idx_queue_route_status_pos;
DROP INDEX IF EXISTS public.idx_queue_departure_time;
DROP INDEX IF EXISTS public.idx_fares_route_discount;
DROP INDEX IF EXISTS public.idx_fare_discounts_terminal_active;
DROP INDEX IF EXISTS public.idx_email;
DROP INDEX IF EXISTS public.idx_departure_rules_terminal_route_time;
DROP INDEX IF EXISTS public.idx_audit_logs_timestamp;
DROP INDEX IF EXISTS public.idx_audit_logs_action;
DROP INDEX IF EXISTS public.idx_announcements_severity;
DROP INDEX IF EXISTS public.idx_announcements_active_sort;
DROP INDEX IF EXISTS public.announcements_is_active;
ALTER TABLE IF EXISTS ONLY public.vehicles DROP CONSTRAINT IF EXISTS vehicles_plate_number_key;
ALTER TABLE IF EXISTS ONLY public.vehicle_types DROP CONSTRAINT IF EXISTS vehicle_types_slug;
ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_username_key;
ALTER TABLE IF EXISTS ONLY public.user_routes DROP CONSTRAINT IF EXISTS unique_user_route;
ALTER TABLE IF EXISTS ONLY public.routes DROP CONSTRAINT IF EXISTS unique_terminal_destination_vehicle;
ALTER TABLE IF EXISTS ONLY public.fares DROP CONSTRAINT IF EXISTS unique_route_discount;
ALTER TABLE IF EXISTS ONLY public.system_settings DROP CONSTRAINT IF EXISTS system_settings_setting_key;
ALTER TABLE IF EXISTS ONLY public.vehicles DROP CONSTRAINT IF EXISTS pk_vehicles;
ALTER TABLE IF EXISTS ONLY public.vehicle_types DROP CONSTRAINT IF EXISTS pk_vehicle_types;
ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS pk_users;
ALTER TABLE IF EXISTS ONLY public.user_routes DROP CONSTRAINT IF EXISTS pk_user_routes;
ALTER TABLE IF EXISTS ONLY public.terminals DROP CONSTRAINT IF EXISTS pk_terminals;
ALTER TABLE IF EXISTS ONLY public.system_settings DROP CONSTRAINT IF EXISTS pk_system_settings;
ALTER TABLE IF EXISTS ONLY public.routes DROP CONSTRAINT IF EXISTS pk_routes;
ALTER TABLE IF EXISTS ONLY public.queue DROP CONSTRAINT IF EXISTS pk_queue;
ALTER TABLE IF EXISTS ONLY public.password_reset_tokens DROP CONSTRAINT IF EXISTS pk_password_reset_tokens;
ALTER TABLE IF EXISTS ONLY public.migrations DROP CONSTRAINT IF EXISTS pk_migrations;
ALTER TABLE IF EXISTS ONLY public.fare_discounts DROP CONSTRAINT IF EXISTS pk_fare_discounts;
ALTER TABLE IF EXISTS ONLY public.departure_rules DROP CONSTRAINT IF EXISTS pk_departure_rules;
ALTER TABLE IF EXISTS ONLY public.audit_logs DROP CONSTRAINT IF EXISTS pk_audit_logs;
ALTER TABLE IF EXISTS ONLY public.announcements DROP CONSTRAINT IF EXISTS pk_announcements;
ALTER TABLE IF EXISTS ONLY public.fares DROP CONSTRAINT IF EXISTS fares_pkey;
ALTER TABLE IF EXISTS ONLY public.fare_discounts DROP CONSTRAINT IF EXISTS fare_discounts_terminal_type_unique;
ALTER TABLE IF EXISTS public.vehicles ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.vehicle_types ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.users ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.user_routes ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.terminals ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.system_settings ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.routes ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.queue ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.password_reset_tokens ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.migrations ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.fares ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.fare_discounts ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.departure_rules ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.audit_logs ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.announcements ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE IF EXISTS public.vehicles_id_seq;
DROP TABLE IF EXISTS public.vehicles;
DROP SEQUENCE IF EXISTS public.vehicle_types_id_seq;
DROP TABLE IF EXISTS public.vehicle_types;
DROP SEQUENCE IF EXISTS public.users_id_seq;
DROP TABLE IF EXISTS public.users;
DROP SEQUENCE IF EXISTS public.user_routes_id_seq;
DROP TABLE IF EXISTS public.user_routes;
DROP SEQUENCE IF EXISTS public.terminals_id_seq;
DROP TABLE IF EXISTS public.terminals;
DROP SEQUENCE IF EXISTS public.system_settings_id_seq;
DROP TABLE IF EXISTS public.system_settings;
DROP SEQUENCE IF EXISTS public.routes_id_seq;
DROP TABLE IF EXISTS public.routes;
DROP SEQUENCE IF EXISTS public.queue_id_seq;
DROP TABLE IF EXISTS public.queue;
DROP SEQUENCE IF EXISTS public.password_reset_tokens_id_seq;
DROP TABLE IF EXISTS public.password_reset_tokens;
DROP SEQUENCE IF EXISTS public.migrations_id_seq;
DROP TABLE IF EXISTS public.migrations;
DROP SEQUENCE IF EXISTS public.fares_id_seq;
DROP TABLE IF EXISTS public.fares;
DROP SEQUENCE IF EXISTS public.fare_discounts_id_seq;
DROP TABLE IF EXISTS public.fare_discounts;
DROP SEQUENCE IF EXISTS public.departure_rules_id_seq;
DROP TABLE IF EXISTS public.departure_rules;
DROP SEQUENCE IF EXISTS public.audit_logs_id_seq;
DROP TABLE IF EXISTS public.audit_logs;
DROP SEQUENCE IF EXISTS public.announcements_id_seq;
DROP TABLE IF EXISTS public.announcements;
-- *not* dropping schema, since initdb creates it
--
-- Name: public; Type: SCHEMA; Schema: -; Owner: -
--

-- *not* creating schema, since initdb creates it


--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: -
--



SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: announcements; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.announcements (
    id integer NOT NULL,
    message text NOT NULL,
    is_active smallint DEFAULT 1 NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    terminal_id integer DEFAULT 1 NOT NULL,
    severity character varying(20) DEFAULT 'info'::character varying NOT NULL
);


--
-- Name: announcements_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.announcements_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: announcements_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.announcements_id_seq OWNED BY public.announcements.id;


--
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.audit_logs (
    id integer NOT NULL,
    user_id integer,
    action character varying(255) NOT NULL,
    details text NOT NULL,
    "timestamp" timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: audit_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.audit_logs_id_seq
    AS integer
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
-- Name: departure_rules; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.departure_rules (
    id integer NOT NULL,
    time_from time without time zone NOT NULL,
    time_to time without time zone NOT NULL,
    wait_minutes integer DEFAULT 30 NOT NULL,
    label character varying(50),
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    terminal_id integer DEFAULT 1 NOT NULL,
    route_id integer
);


--
-- Name: departure_rules_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.departure_rules_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: departure_rules_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.departure_rules_id_seq OWNED BY public.departure_rules.id;


--
-- Name: fare_discounts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fare_discounts (
    id integer NOT NULL,
    type character varying(50) NOT NULL,
    label character varying(100) NOT NULL,
    discount_percent numeric(5,2) DEFAULT 0 NOT NULL,
    is_active smallint DEFAULT 1 NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    terminal_id integer DEFAULT 1 NOT NULL
);


--
-- Name: fare_discounts_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.fare_discounts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: fare_discounts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.fare_discounts_id_seq OWNED BY public.fare_discounts.id;


--
-- Name: fares; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fares (
    id integer NOT NULL,
    route_id integer NOT NULL,
    fare_discount_id integer NOT NULL,
    amount numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: fares_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.fares_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: fares_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.fares_id_seq OWNED BY public.fares.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id bigint NOT NULL,
    version character varying(255) NOT NULL,
    class character varying(255) NOT NULL,
    "group" character varying(255) NOT NULL,
    namespace character varying(255) NOT NULL,
    "time" integer NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
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
    id integer NOT NULL,
    username character varying(50) NOT NULL,
    token character varying(64) NOT NULL,
    expires_at timestamp without time zone NOT NULL,
    used smallint DEFAULT 0 NOT NULL,
    created_at timestamp without time zone,
    reset_code character varying(6),
    email character varying(100),
    verified smallint DEFAULT 0,
    code_attempts integer DEFAULT 0 NOT NULL,
    user_id integer
);


--
-- Name: password_reset_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.password_reset_tokens_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: password_reset_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.password_reset_tokens_id_seq OWNED BY public.password_reset_tokens.id;


--
-- Name: queue; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.queue (
    id integer NOT NULL,
    vehicle_id integer NOT NULL,
    route_id integer NOT NULL,
    status character varying(20) DEFAULT 'waiting'::character varying NOT NULL,
    current_passengers integer DEFAULT 0 NOT NULL,
    "position" integer DEFAULT 0 NOT NULL,
    arrival_time timestamp without time zone,
    departure_time timestamp without time zone,
    estimated_departure timestamp without time zone,
    driver_name character varying(100),
    operator_name character varying(100),
    plate_number character varying(50)
);


--
-- Name: queue_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.queue_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: queue_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.queue_id_seq OWNED BY public.queue.id;


--
-- Name: routes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.routes (
    id integer NOT NULL,
    destination character varying(100) NOT NULL,
    vehicle_type character varying(50) DEFAULT 'van'::character varying NOT NULL,
    terminal_id integer NOT NULL,
    created_at timestamp without time zone,
    status character varying(20) DEFAULT 'active'::character varying NOT NULL
);


--
-- Name: routes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.routes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: routes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.routes_id_seq OWNED BY public.routes.id;


--
-- Name: system_settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.system_settings (
    id integer NOT NULL,
    setting_key character varying(100) NOT NULL,
    setting_value text,
    created_at timestamp without time zone,
    updated_at timestamp without time zone
);


--
-- Name: system_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.system_settings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: system_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.system_settings_id_seq OWNED BY public.system_settings.id;


--
-- Name: terminals; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.terminals (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    location character varying(255) NOT NULL,
    capacity integer DEFAULT 0 NOT NULL,
    created_at timestamp without time zone
);


--
-- Name: terminals_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.terminals_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: terminals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.terminals_id_seq OWNED BY public.terminals.id;


--
-- Name: user_routes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_routes (
    id integer NOT NULL,
    user_id integer NOT NULL,
    route_id integer NOT NULL,
    created_at timestamp without time zone
);


--
-- Name: user_routes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.user_routes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: user_routes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.user_routes_id_seq OWNED BY public.user_routes.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id integer NOT NULL,
    username character varying(50) NOT NULL,
    password_hash character varying(255) NOT NULL,
    role character varying(20) DEFAULT 'staff'::character varying NOT NULL,
    full_name character varying(100) NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    login_attempts smallint DEFAULT 0 NOT NULL,
    locked_until timestamp without time zone,
    email character varying(100),
    profile_image character varying(255) DEFAULT NULL::character varying,
    status character varying(20) DEFAULT 'active'::character varying NOT NULL
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    AS integer
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
-- Name: vehicle_types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.vehicle_types (
    id integer NOT NULL,
    name character varying(80) NOT NULL,
    slug character varying(50) NOT NULL,
    is_active smallint DEFAULT 1 NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    color character varying(20) DEFAULT '#0284c7'::character varying,
    icon character varying(50) DEFAULT 'fa-bus'::character varying,
    photo character varying(255) DEFAULT NULL::character varying
);


--
-- Name: vehicle_types_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.vehicle_types_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: vehicle_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.vehicle_types_id_seq OWNED BY public.vehicle_types.id;


--
-- Name: vehicles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.vehicles (
    id integer NOT NULL,
    plate_number character varying(20) NOT NULL,
    type character varying(50) NOT NULL,
    default_route_id integer,
    capacity integer NOT NULL,
    owner_name character varying(100) NOT NULL,
    scheduled_departure_time time without time zone,
    status character varying(20) DEFAULT 'active'::character varying NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    driver_name character varying(100),
    operator_name character varying(100),
    photo character varying(255) DEFAULT NULL::character varying
);


--
-- Name: vehicles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.vehicles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: vehicles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.vehicles_id_seq OWNED BY public.vehicles.id;


--
-- Name: announcements id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcements ALTER COLUMN id SET DEFAULT nextval('public.announcements_id_seq'::regclass);


--
-- Name: audit_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN id SET DEFAULT nextval('public.audit_logs_id_seq'::regclass);


--
-- Name: departure_rules id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.departure_rules ALTER COLUMN id SET DEFAULT nextval('public.departure_rules_id_seq'::regclass);


--
-- Name: fare_discounts id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fare_discounts ALTER COLUMN id SET DEFAULT nextval('public.fare_discounts_id_seq'::regclass);


--
-- Name: fares id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fares ALTER COLUMN id SET DEFAULT nextval('public.fares_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: password_reset_tokens id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens ALTER COLUMN id SET DEFAULT 
nextval('public.password_reset_tokens_id_seq'::regclass);


--
-- Name: queue id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.queue ALTER COLUMN id SET DEFAULT nextval('public.queue_id_seq'::regclass);


--
-- Name: routes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routes ALTER COLUMN id SET DEFAULT nextval('public.routes_id_seq'::regclass);


--
-- Name: system_settings id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.system_settings ALTER COLUMN id SET DEFAULT nextval('public.system_settings_id_seq'::regclass);


--
-- Name: terminals id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.terminals ALTER COLUMN id SET DEFAULT nextval('public.terminals_id_seq'::regclass);


--
-- Name: user_routes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_routes ALTER COLUMN id SET DEFAULT nextval('public.user_routes_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: vehicle_types id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vehicle_types ALTER COLUMN id SET DEFAULT nextval('public.vehicle_types_id_seq'::regclass);


--
-- Name: vehicles id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vehicles ALTER COLUMN id SET DEFAULT nextval('public.vehicles_id_seq'::regclass);


--
-- Data for Name: announcements; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.announcements VALUES (1, 'Jaylo Gwapodfdfdf', 1, 0, '2026-09-03 18:56:08', '2026-09-04 13:58:02', 
1, 'info');
INSERT INTO public.announcements VALUES (2, 'erere', 1, 1, '2026-09-04 20:02:21', '2026-09-04 20:02:21', 1, 'info');


--
-- Data for Name: audit_logs; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.audit_logs VALUES (2, 1, 'Login', 'Super_admin logged in (admin)', '2026-09-03 18:05:47.652859');
INSERT INTO public.audit_logs VALUES (3, 1, 'Logout', 'Super_admin admin logged out', '2026-09-03 18:05:47.652859');
INSERT INTO public.audit_logs VALUES (4, 1, 'Login', 'Super_admin logged in (admin)', '2026-09-03 18:05:47.652859');
INSERT INTO public.audit_logs VALUES (5, 1, 'Create terminal', 'Added terminal: Palompon Terminal', '2026-09-03 
18:05:47.652859');
INSERT INTO public.audit_logs VALUES (6, 1, 'Create route', 'PALOMPON TERMINAL → ORMOC (3 vehicle type(s)).', 
'2026-09-03 18:05:47.652859');
INSERT INTO public.audit_logs VALUES (7, 1, 'Assign vehicle to route', 'Registered vehicle 123135 (van) - Operator: 
Terrado - Driver: Jaylo - Route: PALOMPON TERMINAL → ORMOC', '2026-09-03 18:05:47.652859');
INSERT INTO public.audit_logs VALUES (8, 1, 'Update terminal', 'Updated terminal: Palompon', '2026-09-03 
18:05:47.652859');
INSERT INTO public.audit_logs VALUES (9, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-03 
18:05:47.652859');
INSERT INTO public.audit_logs VALUES (10, 1, 'Update dispatcher routes', 'Updated dispatcher "jycgrac@gmail.com". 
Added: PALOMPON → ORMOC.', '2026-09-03 18:12:26');
INSERT INTO public.audit_logs VALUES (11, 1, 'Update dispatcher routes', 'Updated dispatcher "noynayjaylo@gmail.com". 
Added: PALOMPON → ORMOC.', '2026-09-03 18:15:07');
INSERT INTO public.audit_logs VALUES (12, 1, 'Create route', 'PALOMPON → TACLOBAN (3 vehicle type(s)).', '2026-09-03 
18:18:03');
INSERT INTO public.audit_logs VALUES (14, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-03 18:50:01');
INSERT INTO public.audit_logs VALUES (15, 1, 'Create announcement', 'Jaylo Gwapo', '2026-09-03 18:56:08');
INSERT INTO public.audit_logs VALUES (16, 3, 'Add to queue', 'Added 123135 to queue for ORMOC. (Operator: Terrado, 
Driver: Jaylo). Rule: Evening (40 min).', '2026-09-03 19:07:12');
INSERT INTO public.audit_logs VALUES (17, 1, 'Update vehicle type', 'Updated vehicle type Van to Van.', '2026-09-03 
19:07:34');
INSERT INTO public.audit_logs VALUES (18, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-03 19:07:43');
INSERT INTO public.audit_logs VALUES (19, 3, 'Start Boarding', 'Start Boarding for 123135 (ORMOC). (Operator: Terrado, 
Driver: Jaylo)', '2026-09-03 19:08:20');
INSERT INTO public.audit_logs VALUES (20, 3, 'Updated Driver', 'Updated Driver: Jaylo for 123135 (van) to TerradO.', 
'2026-09-03 19:08:29');
INSERT INTO public.audit_logs VALUES (21, 3, 'Updated Driver', 'Updated Driver: TerradO for 123135 (van) to Terrado.', 
'2026-09-03 19:08:35');
INSERT INTO public.audit_logs VALUES (22, 1, 'Update discount', 'senior_citizen discount updated to 20.00%.', 
'2026-09-03 19:08:45');
INSERT INTO public.audit_logs VALUES (23, 1, 'Assign vehicle to route', 'Registered vehicle 2321232 (minibus) - 
Operator: JKDjflkdjf - Driver: fddfd - Route: PALOMPON → ORMOC', '2026-09-03 19:20:06');
INSERT INTO public.audit_logs VALUES (24, 1, 'Assign vehicle to route', 'Registered vehicle 23232321 (jeepney) - 
Operator: 324234 - Driver: 1232a - Route: PALOMPON → ORMOC', '2026-09-03 19:20:18');
INSERT INTO public.audit_logs VALUES (25, 1, 'Add vehicle type', 'Added vehicle type Bus (#ca8a04, fa-bus-simple).', 
'2026-09-03 19:22:43');
INSERT INTO public.audit_logs VALUES (26, 1, 'Update route group', 'PALOMPON → TACLOBAN.', '2026-09-03 19:23:18');
INSERT INTO public.audit_logs VALUES (27, 1, 'Assign vehicle to route', 'Registered vehicle 2323232133 (bus) - 
Operator: Jaylo - Driver: Ter - Route: PALOMPON → TACLOBAN', '2026-09-03 19:23:45');
INSERT INTO public.audit_logs VALUES (28, 3, 'Cancel Trip', 'Cancel Trip for 123135 (ORMOC). (Operator: Terrado, 
Driver: Terrado)', '2026-09-03 19:25:37');
INSERT INTO public.audit_logs VALUES (29, 3, 'Add to queue', 'Added 23232321 to queue for ORMOC. (Operator: 324234, 
Driver: 1232a). Rule: Evening (40 min).', '2026-09-03 19:25:41');
INSERT INTO public.audit_logs VALUES (30, 3, 'Add to queue', 'Added 2321232 to queue for ORMOC. (Operator: JKDjflkdjf, 
Driver: fddfd). Rule: Evening (40 min).', '2026-09-03 19:25:41');
INSERT INTO public.audit_logs VALUES (31, 3, 'Add to queue', 'Added 123135 to queue for ORMOC. (Operator: Terrado, 
Driver: Terrado). Rule: Evening (40 min).', '2026-09-03 19:25:41');
INSERT INTO public.audit_logs VALUES (32, 3, 'Cancel Trip', 'Cancel Trip for 23232321 (ORMOC). (Operator: 324234, 
Driver: 1232a)', '2026-09-03 19:25:58');
INSERT INTO public.audit_logs VALUES (33, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDjflkdjf, 
Driver: fddfd)', '2026-09-03 19:26:07');
INSERT INTO public.audit_logs VALUES (34, 3, 'Cancel Trip', 'Cancel Trip for 123135 (ORMOC). (Operator: Terrado, 
Driver: Terrado)', '2026-09-03 19:26:09');
INSERT INTO public.audit_logs VALUES (35, 3, 'Add to queue', 'Added 123135 to queue for ORMOC. (Operator: Terrado, 
Driver: Terrado). Rule: Evening (40 min).', '2026-09-03 19:26:13');
INSERT INTO public.audit_logs VALUES (36, 3, 'Add to queue', 'Added 2321232 to queue for ORMOC. (Operator: JKDjflkdjf, 
Driver: fddfd). Rule: Evening (40 min).', '2026-09-03 19:26:13');
INSERT INTO public.audit_logs VALUES (37, 3, 'Add to queue', 'Added 23232321 to queue for ORMOC. (Operator: 324234, 
Driver: 1232a). Rule: Evening (40 min).', '2026-09-03 19:26:13');
INSERT INTO public.audit_logs VALUES (38, 3, 'Start Boarding', 'Start Boarding for 123135 (ORMOC). (Operator: Terrado, 
Driver: Terrado)', '2026-09-03 19:26:17');
INSERT INTO public.audit_logs VALUES (39, 1, 'Update dispatcher routes', 'Updated dispatcher "jycgrac@gmail.com". 
Added: PALOMPON → TACLOBAN.', '2026-09-03 19:26:42');
INSERT INTO public.audit_logs VALUES (40, 3, 'Add to queue', 'Added 2323232133 to queue for TACLOBAN. (Operator: 
Jaylo, Driver: Ter). Rule: Evening (40 min).', '2026-09-03 19:26:51');
INSERT INTO public.audit_logs VALUES (41, 3, 'Cancel Trip', 'Cancel Trip for 123135 (ORMOC). (Operator: Terrado, 
Driver: Terrado)', '2026-09-03 19:27:02');
INSERT INTO public.audit_logs VALUES (42, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDjflkdjf, 
Driver: fddfd)', '2026-09-03 19:27:04');
INSERT INTO public.audit_logs VALUES (43, 3, 'Cancel Trip', 'Cancel Trip for 23232321 (ORMOC). (Operator: 324234, 
Driver: 1232a)', '2026-09-03 19:27:09');
INSERT INTO public.audit_logs VALUES (44, 3, 'Cancel Trip', 'Cancel Trip for 2323232133 (TACLOBAN). (Operator: Jaylo, 
Driver: Ter)', '2026-09-03 19:27:12');
INSERT INTO public.audit_logs VALUES (45, 3, 'Add to queue', 'Added 2323232133 to queue for TACLOBAN. (Operator: 
Jaylo, Driver: Ter). Rule: Evening (40 min).', '2026-09-03 19:27:35');
INSERT INTO public.audit_logs VALUES (46, 3, 'Add to queue', 'Added 123135 to queue for ORMOC. (Operator: Terrado, 
Driver: Terrado). Rule: Evening (40 min).', '2026-09-03 19:27:35');
INSERT INTO public.audit_logs VALUES (47, 3, 'Add to queue', 'Added 2321232 to queue for ORMOC. (Operator: JKDjflkdjf, 
Driver: fddfd). Rule: Evening (40 min).', '2026-09-03 19:27:35');
INSERT INTO public.audit_logs VALUES (48, 3, 'Add to queue', 'Added 23232321 to queue for ORMOC. (Operator: 324234, 
Driver: 1232a). Rule: Evening (40 min).', '2026-09-03 19:27:35');
INSERT INTO public.audit_logs VALUES (49, 3, 'Start Boarding', 'Start Boarding for 123135 (ORMOC). (Operator: Terrado, 
Driver: Terrado)', '2026-09-03 19:28:51');
INSERT INTO public.audit_logs VALUES (50, 3, 'Depart Vehicle', 'Depart Vehicle for 123135 (ORMOC). (Operator: Terrado, 
Driver: Terrado)', '2026-09-03 19:29:04');
INSERT INTO public.audit_logs VALUES (51, 3, 'Start Boarding', 'Start Boarding for 2321232 (ORMOC). (Operator: 
JKDjflkdjf, Driver: fddfd)', '2026-09-03 19:29:20');
INSERT INTO public.audit_logs VALUES (52, 3, 'Depart Vehicle', 'Depart Vehicle for 2321232 (ORMOC). (Operator: 
JKDjflkdjf, Driver: fddfd)', '2026-09-03 19:29:23');
INSERT INTO public.audit_logs VALUES (53, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-04 12:02:26');
INSERT INTO public.audit_logs VALUES (54, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-04 
12:03:26');
INSERT INTO public.audit_logs VALUES (55, 1, 'Update announcement', 'ID 1: Jaylo Gwapodfdfdf', '2026-09-04 12:03:41');
INSERT INTO public.audit_logs VALUES (56, 1, 'Update vehicle type', 'Updated vehicle type Jeepney to Jeepney.', 
'2026-09-04 12:04:27');
INSERT INTO public.audit_logs VALUES (57, 1, 'Update vehicle type', 'Updated vehicle type Jeepney to Jeepney.', 
'2026-09-04 12:04:40');
INSERT INTO public.audit_logs VALUES (58, 3, 'Start Boarding', 'Start Boarding for 23232321 (ORMOC). (Operator: 
324234, Driver: 1232a)', '2026-09-04 12:15:23');
INSERT INTO public.audit_logs VALUES (59, 3, 'Depart Vehicle', 'Depart Vehicle for 23232321 (ORMOC). (Operator: 
324234, Driver: 1232a)', '2026-09-04 12:15:27');
INSERT INTO public.audit_logs VALUES (60, 1, 'Update vehicle type', 'Updated vehicle type Bus to Bus.', '2026-09-04 
12:18:49');
INSERT INTO public.audit_logs VALUES (61, 1, 'Update vehicle type', 'Updated vehicle type Jeepney to Jeepney.', 
'2026-09-04 12:19:18');
INSERT INTO public.audit_logs VALUES (62, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-04 12:19:28');
INSERT INTO public.audit_logs VALUES (63, 1, 'Update vehicle type', 'Updated vehicle type Van to Van.', '2026-09-04 
12:19:42');
INSERT INTO public.audit_logs VALUES (64, 3, 'Add to queue', 'Added 2321232 to queue for ORMOC. (Operator: JKDjflkdjf, 
Driver: fddfd). Rule: Afternoon (40 min).', '2026-09-04 12:21:13');
INSERT INTO public.audit_logs VALUES (65, 1, 'Update vehicle type', 'Updated vehicle type Bus to Bus.', '2026-09-04 
12:21:51');
INSERT INTO public.audit_logs VALUES (66, 3, 'Start Boarding', 'Start Boarding for 2321232 (ORMOC). (Operator: 
JKDjflkdjf, Driver: fddfd)', '2026-09-04 12:26:45');
INSERT INTO public.audit_logs VALUES (67, 3, 'Depart Vehicle', 'Depart Vehicle for 2321232 (ORMOC). (Operator: 
JKDjflkdjf, Driver: fddfd)', '2026-09-04 12:29:28');
INSERT INTO public.audit_logs VALUES (68, 3, 'Add to queue', 'Added 123135 to queue for ORMOC. (Operator: Terrado, 
Driver: Terrado). Rule: Afternoon (40 min).', '2026-09-04 12:45:13');
INSERT INTO public.audit_logs VALUES (69, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-04 12:59:30');
INSERT INTO public.audit_logs VALUES (70, 1, 'Update vehicle type', 'Updated vehicle type Van to Van.', '2026-09-04 
12:59:51');
INSERT INTO public.audit_logs VALUES (71, 1, 'Create terminal', 'Added terminal: Cebu', '2026-09-04 13:10:30');
INSERT INTO public.audit_logs VALUES (72, 1, 'Update discount', 'senior_citizen discount updated to 20.00%.', 
'2026-09-04 13:36:06');
INSERT INTO public.audit_logs VALUES (73, 1, 'Update discount', 'senior_citizen discount updated to 20.00%.', 
'2026-09-04 13:36:10');
INSERT INTO public.audit_logs VALUES (74, 1, 'Update route', 'PALOMPON to TACLOBAN (van).', '2026-09-04 13:56:25');
INSERT INTO public.audit_logs VALUES (75, 1, 'Update announcement', 'ID 1: Jaylo Gwapodfdfdf', '2026-09-04 13:58:02');
INSERT INTO public.audit_logs VALUES (76, 1, 'Update route group', 'PALOMPON → ORMOC.', '2026-09-04 14:49:19');
INSERT INTO public.audit_logs VALUES (77, 1, 'Reassign vehicle route', 'Reassigned 2323232133 from PALOMPON → TACLOBAN 
to PALOMPON → ORMOC', '2026-09-04 14:49:27');
INSERT INTO public.audit_logs VALUES (78, 3, 'Cancel Trip', 'Cancel Trip for 123135 (ORMOC). (Operator: Terrado, 
Driver: Terrado)', '2026-09-04 14:49:46');
INSERT INTO public.audit_logs VALUES (79, 3, 'Cancel Trip', 'Cancel Trip for 2323232133 (TACLOBAN). (Operator: Jaylo, 
Driver: Ter)', '2026-09-04 14:49:49');
INSERT INTO public.audit_logs VALUES (80, 1, 'Assign vehicle to route', 'Registered vehicle 123123 (bus) - Operator: 
Jaylo - Driver: Terad - Route: PALOMPON → ORMOC', '2026-09-04 14:50:45');
INSERT INTO public.audit_logs VALUES (81, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-04 15:03:46');
INSERT INTO public.audit_logs VALUES (82, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-04 15:04:34');
INSERT INTO public.audit_logs VALUES (83, 1, 'Update vehicle type', 'Updated vehicle type Bus to Bus.', '2026-09-04 
15:18:51');
INSERT INTO public.audit_logs VALUES (84, 1, 'Reassign vehicle route', 'Reassigned 123123 from PALOMPON → ORMOC to 
PALOMPON → TACLOBAN', '2026-09-04 15:19:09');
INSERT INTO public.audit_logs VALUES (85, 1, 'Delete vehicle type', 'Deleted vehicle type Bus (bus) and associated 
routes, fares, and vehicles.', '2026-09-04 16:38:42');
INSERT INTO public.audit_logs VALUES (86, 1, 'Delete route', 'Palompon → TACLOBAN.', '2026-09-04 16:39:03');
INSERT INTO public.audit_logs VALUES (87, 1, 'Delete route', 'Palompon → ORMOC.', '2026-09-04 16:39:07');
INSERT INTO public.audit_logs VALUES (88, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-04 19:33:36');
INSERT INTO public.audit_logs VALUES (89, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-04 
19:34:06');
INSERT INTO public.audit_logs VALUES (90, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-04 
19:40:43');
INSERT INTO public.audit_logs VALUES (91, 1, 'Logout', 'Super_admin arclast988@gmail.com logged out', '2026-09-04 
19:42:35');
INSERT INTO public.audit_logs VALUES (92, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-04 
19:43:07');
INSERT INTO public.audit_logs VALUES (93, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-04 19:51:30');
INSERT INTO public.audit_logs VALUES (94, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-04 
19:51:42');
INSERT INTO public.audit_logs VALUES (95, 1, 'Delete route', 'Palompon → ORMOC.', '2026-09-04 20:01:11');
INSERT INTO public.audit_logs VALUES (96, 1, 'Create announcement', 'erere', '2026-09-04 20:02:21');
INSERT INTO public.audit_logs VALUES (99, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-16 
06:08:42');
INSERT INTO public.audit_logs VALUES (100, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-16 06:12:32');
INSERT INTO public.audit_logs VALUES (101, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-16 06:12:35');
INSERT INTO public.audit_logs VALUES (102, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-16 06:12:37');
INSERT INTO public.audit_logs VALUES (103, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-16 06:12:39');
INSERT INTO public.audit_logs VALUES (104, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-16 
06:25:37');
INSERT INTO public.audit_logs VALUES (105, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-16 06:35:24');
INSERT INTO public.audit_logs VALUES (106, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-16 06:35:40');
INSERT INTO public.audit_logs VALUES (107, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-16 06:35:49');
INSERT INTO public.audit_logs VALUES (108, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-16 06:36:01');
INSERT INTO public.audit_logs VALUES (109, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-16 06:36:22');
INSERT INTO public.audit_logs VALUES (110, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-16 06:36:46');
INSERT INTO public.audit_logs VALUES (111, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-16 16:34:21');
INSERT INTO public.audit_logs VALUES (112, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-16 
16:34:32');
INSERT INTO public.audit_logs VALUES (113, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transportation)', '2026-09-16 17:19:31');
INSERT INTO public.audit_logs VALUES (114, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transportation)', '2026-09-16 17:19:36');
INSERT INTO public.audit_logs VALUES (115, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transportation)', '2026-09-16 17:20:29');
INSERT INTO public.audit_logs VALUES (116, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transportation)', '2026-09-16 17:20:37');
INSERT INTO public.audit_logs VALUES (117, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transit)', '2026-09-16 17:24:25');
INSERT INTO public.audit_logs VALUES (118, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transit)', '2026-09-16 17:24:44');
INSERT INTO public.audit_logs VALUES (119, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transits)', '2026-09-16 17:37:22');
INSERT INTO public.audit_logs VALUES (120, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transitss)', '2026-09-16 17:53:58');
INSERT INTO public.audit_logs VALUES (121, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transit)', '2026-09-16 17:54:04');
INSERT INTO public.audit_logs VALUES (122, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-16 17:54:15');
INSERT INTO public.audit_logs VALUES (123, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-16 17:54:17');
INSERT INTO public.audit_logs VALUES (124, 1, 'Update vehicle type', 'Updated vehicle type Jeepney to Jeepney.', 
'2026-09-16 17:54:39');
INSERT INTO public.audit_logs VALUES (125, 1, 'Update vehicle type', 'Updated vehicle type Jeepney to Jeepney.', 
'2026-09-16 17:55:09');
INSERT INTO public.audit_logs VALUES (126, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-16 17:55:23');
INSERT INTO public.audit_logs VALUES (127, 3, 'Add to queue', 'Added 2321232 to queue for ORMOC. (Operator: 
JKDJFLKDJF, Driver: fddfd). Rule: Afternoon Rush (30 min).', '2026-09-16 17:55:29');
INSERT INTO public.audit_logs VALUES (128, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-16 18:17:41');
INSERT INTO public.audit_logs VALUES (129, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-16 18:17:56');
INSERT INTO public.audit_logs VALUES (130, 1, 'System Settings', 'Uploaded new system logo: 
logo_1789554115_5d604efd.jpg', '2026-09-16 18:21:55');
INSERT INTO public.audit_logs VALUES (131, 1, 'System Settings', 'Reset system logo to default', '2026-09-16 
18:21:59');
INSERT INTO public.audit_logs VALUES (132, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transits)', '2026-09-16 18:27:05');
INSERT INTO public.audit_logs VALUES (133, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transitssssssssssssssssssssssssssssssssssss)', '2026-09-16 18:27:16');
INSERT INTO public.audit_logs VALUES (134, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transit)', '2026-09-16 18:28:08');
INSERT INTO public.audit_logs VALUES (135, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transportation)', '2026-09-16 18:29:22');
INSERT INTO public.audit_logs VALUES (136, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transit)', '2026-09-16 18:29:54');
INSERT INTO public.audit_logs VALUES (137, 1, 'Deactivate user', 'Deactivated user "System Administrator" (admin) and 
moved to archive', '2026-09-16 18:32:06');
INSERT INTO public.audit_logs VALUES (138, 1, 'Delete user', 'Permanently deleted user "System Administrator" 
(admin)', '2026-09-16 18:32:11');
INSERT INTO public.audit_logs VALUES (139, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-16 18:39:42');
INSERT INTO public.audit_logs VALUES (140, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-17 
05:22:31');
INSERT INTO public.audit_logs VALUES (141, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transpotation)', '2026-09-17 05:28:59');
INSERT INTO public.audit_logs VALUES (142, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transpotation Jaylo gwappoo)', '2026-09-17 05:29:25');
INSERT INTO public.audit_logs VALUES (143, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-17 05:29:53');
INSERT INTO public.audit_logs VALUES (144, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transpotation Jaylo gwappooeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee)', '2026-09-17 05:30:19');
INSERT INTO public.audit_logs VALUES (145, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transit)', '2026-09-17 05:34:29');
INSERT INTO public.audit_logs VALUES (146, 1, 'Reassign vehicle route', 'Reassigned 23232321 from None to PALOMPON → 
TACLOBAN', '2026-09-17 05:35:48');
INSERT INTO public.audit_logs VALUES (147, 1, 'Add vehicle type', 'Added vehicle type Bus (#c62828, fa-bus-simple).', 
'2026-09-17 05:52:52');
INSERT INTO public.audit_logs VALUES (148, 1, 'Add vehicle type', 'Added vehicle type Taxi (#1565c0, fa-taxi).', 
'2026-09-17 05:53:13');
INSERT INTO public.audit_logs VALUES (149, 1, 'Add vehicle type', 'Added vehicle type Tricecle (#2e7d32, 
fa-motorcycle).', '2026-09-17 05:53:32');
INSERT INTO public.audit_logs VALUES (150, 1, 'Update vehicle', 'Updated vehicle 23232321', '2026-09-17 05:57:24');
INSERT INTO public.audit_logs VALUES (151, 1, 'Update vehicle', 'Updated vehicle 23232321', '2026-09-17 05:57:39');
INSERT INTO public.audit_logs VALUES (152, 1, 'Update vehicle', 'Updated vehicle 23232321', '2026-09-17 05:57:56');
INSERT INTO public.audit_logs VALUES (153, 1, 'Update vehicle', 'Updated vehicle 23232321', '2026-09-17 05:59:49');
INSERT INTO public.audit_logs VALUES (154, 1, 'Update vehicle', 'Updated vehicle 2321232', '2026-09-17 06:13:48');
INSERT INTO public.audit_logs VALUES (155, 1, 'Update vehicle', 'Updated vehicle 2321232', '2026-09-17 06:14:14');
INSERT INTO public.audit_logs VALUES (156, 1, 'Update vehicle', 'Updated vehicle 2321232', '2026-09-17 06:14:33');
INSERT INTO public.audit_logs VALUES (157, 3, 'Start Boarding', 'Start Boarding for 2321232 (ORMOC). (Operator: 
JKDJFLKDJF, Driver: fddfd)', '2026-09-17 06:26:36');
INSERT INTO public.audit_logs VALUES (158, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-17 06:26:41');
INSERT INTO public.audit_logs VALUES (159, 3, 'Undo Cancel Trip', 'Restored trip for 2321232 back to the active 
queue.', '2026-09-17 06:26:44');
INSERT INTO public.audit_logs VALUES (160, 3, 'Start Boarding', 'Start Boarding for 2321232 (ORMOC). (Operator: 
JKDJFLKDJF, Driver: fddfd)', '2026-09-17 06:27:02');
INSERT INTO public.audit_logs VALUES (161, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-17 06:27:13');
INSERT INTO public.audit_logs VALUES (162, 3, 'Undo Cancel Trip', 'Restored trip for 2321232 back to the active 
queue.', '2026-09-17 06:27:14');
INSERT INTO public.audit_logs VALUES (163, 1, 'Update vehicle', 'Updated vehicle 2321232', '2026-09-17 06:38:37');
INSERT INTO public.audit_logs VALUES (164, 3, 'Start Boarding', 'Start Boarding for 2321232 (ORMOC). (Operator: 
JKDJFLKDJF, Driver: fddfd)', '2026-09-17 06:39:03');
INSERT INTO public.audit_logs VALUES (165, 1, 'Update vehicle', 'Updated vehicle 2321232', '2026-09-17 06:49:05');
INSERT INTO public.audit_logs VALUES (166, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-17 06:49:57');
INSERT INTO public.audit_logs VALUES (167, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-17 07:10:21');
INSERT INTO public.audit_logs VALUES (168, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-17 07:43:20');
INSERT INTO public.audit_logs VALUES (169, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-17 07:45:15');
INSERT INTO public.audit_logs VALUES (170, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-17 07:47:34');
INSERT INTO public.audit_logs VALUES (171, 3, 'Undo Cancel Trip', 'Restored trip for 2321232 back to the active 
queue.', '2026-09-17 07:47:36');
INSERT INTO public.audit_logs VALUES (172, 3, 'Start Boarding', 'Start Boarding for 2321232 (ORMOC). (Operator: 
JKDJFLKDJF, Driver: fddfd)', '2026-09-17 07:49:50');
INSERT INTO public.audit_logs VALUES (173, 1, 'Update vehicle', 'Updated vehicle 23232321', '2026-09-17 08:20:47');
INSERT INTO public.audit_logs VALUES (174, 1, 'Update vehicle', 'Updated vehicle 23232321', '2026-09-17 08:21:04');
INSERT INTO public.audit_logs VALUES (175, 1, 'Update vehicle type', 'Updated vehicle type Jeepney to Jeepney.', 
'2026-09-17 08:21:15');
INSERT INTO public.audit_logs VALUES (176, 1, 'Update vehicle type', 'Updated vehicle type Jeepney to Jeepney.', 
'2026-09-17 08:21:30');
INSERT INTO public.audit_logs VALUES (177, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-17 08:21:40');
INSERT INTO public.audit_logs VALUES (178, 1, 'Update vehicle', 'Updated vehicle 2321232', '2026-09-17 08:22:05');
INSERT INTO public.audit_logs VALUES (179, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-17 08:22:17');
INSERT INTO public.audit_logs VALUES (180, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-17 08:29:22');
INSERT INTO public.audit_logs VALUES (181, 3, 'Undo Cancel Trip', 'Restored trip for 2321232 back to the active 
queue.', '2026-09-17 08:29:25');
INSERT INTO public.audit_logs VALUES (182, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 08:34:21');
INSERT INTO public.audit_logs VALUES (183, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 08:34:25');
INSERT INTO public.audit_logs VALUES (184, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 08:34:32');
INSERT INTO public.audit_logs VALUES (185, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 08:35:05');
INSERT INTO public.audit_logs VALUES (186, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transitsssssssssssssssssssssssssssssssss)', '2026-09-17 08:37:12');
INSERT INTO public.audit_logs VALUES (187, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transitssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss)', '2026-09-17 08:37:40');
INSERT INTO public.audit_logs VALUES (188, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transit)', '2026-09-17 09:01:13');
INSERT INTO public.audit_logs VALUES (189, 3, 'Start Boarding', 'Start Boarding for 2321232 (ORMOC). (Operator: 
JKDJFLKDJF, Driver: fddfd)', '2026-09-17 09:01:46');
INSERT INTO public.audit_logs VALUES (190, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-17 09:01:50');
INSERT INTO public.audit_logs VALUES (191, 3, 'Undo Cancel Trip', 'Restored trip for 2321232 back to the active 
queue.', '2026-09-17 09:01:53');
INSERT INTO public.audit_logs VALUES (192, 3, 'Start Boarding', 'Start Boarding for 2321232 (ORMOC). (Operator: 
JKDJFLKDJF, Driver: fddfd)', '2026-09-17 09:02:08');
INSERT INTO public.audit_logs VALUES (193, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-17 09:05:08');
INSERT INTO public.audit_logs VALUES (194, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-17 09:05:21');
INSERT INTO public.audit_logs VALUES (195, 1, 'Update vehicle', 'Updated vehicle 2321232', '2026-09-17 09:05:32');
INSERT INTO public.audit_logs VALUES (196, 1, 'Update vehicle', 'Updated vehicle 2321232', '2026-09-17 09:05:59');
INSERT INTO public.audit_logs VALUES (197, 1, 'Assign vehicle to route', 'Registered vehicle 112EF (minibus) - 
Operator: LETRANSCO - Driver: dfdfd - Route: PALOMPON → ORMOC', '2026-09-17 09:08:31');
INSERT INTO public.audit_logs VALUES (198, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transporation jaylogwa and its valitiesssssssssssssssssssssssssssssssss)', '2026-09-17 09:29:28');
INSERT INTO public.audit_logs VALUES (199, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transit)', '2026-09-17 09:46:13');
INSERT INTO public.audit_logs VALUES (200, 2, 'Login', 'Admin logged in (noynayjaylo@gmail.com)', '2026-09-17 
09:47:54');
INSERT INTO public.audit_logs VALUES (201, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-17 
15:21:58');
INSERT INTO public.audit_logs VALUES (202, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 15:22:35');
INSERT INTO public.audit_logs VALUES (203, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 15:22:52');
INSERT INTO public.audit_logs VALUES (204, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 15:23:13');
INSERT INTO public.audit_logs VALUES (205, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 15:23:28');
INSERT INTO public.audit_logs VALUES (206, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 15:23:44');
INSERT INTO public.audit_logs VALUES (207, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 15:27:01');
INSERT INTO public.audit_logs VALUES (208, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 15:27:33');
INSERT INTO public.audit_logs VALUES (209, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 16:01:31');
INSERT INTO public.audit_logs VALUES (210, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 16:01:49');
INSERT INTO public.audit_logs VALUES (211, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-17 
19:52:11');
INSERT INTO public.audit_logs VALUES (212, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-17 
19:54:37');
INSERT INTO public.audit_logs VALUES (213, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 19:55:09');
INSERT INTO public.audit_logs VALUES (214, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 19:55:48');
INSERT INTO public.audit_logs VALUES (215, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 19:56:09');
INSERT INTO public.audit_logs VALUES (216, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 19:56:13');
INSERT INTO public.audit_logs VALUES (217, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 19:56:20');
INSERT INTO public.audit_logs VALUES (218, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 19:57:00');
INSERT INTO public.audit_logs VALUES (219, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 19:57:29');
INSERT INTO public.audit_logs VALUES (220, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 19:57:56');
INSERT INTO public.audit_logs VALUES (245, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:10:50');
INSERT INTO public.audit_logs VALUES (246, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:11:05');
INSERT INTO public.audit_logs VALUES (247, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:11:40');
INSERT INTO public.audit_logs VALUES (248, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:11:46');
INSERT INTO public.audit_logs VALUES (249, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:12:23');
INSERT INTO public.audit_logs VALUES (250, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:12:30');
INSERT INTO public.audit_logs VALUES (251, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-17 20:13:21');
INSERT INTO public.audit_logs VALUES (252, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:17:59');
INSERT INTO public.audit_logs VALUES (253, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:18:36');
INSERT INTO public.audit_logs VALUES (254, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:19:20');
INSERT INTO public.audit_logs VALUES (255, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:19:46');
INSERT INTO public.audit_logs VALUES (256, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:20:44');
INSERT INTO public.audit_logs VALUES (257, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:23:48');
INSERT INTO public.audit_logs VALUES (258, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:24:34');
INSERT INTO public.audit_logs VALUES (259, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:24:52');
INSERT INTO public.audit_logs VALUES (260, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:43:24');
INSERT INTO public.audit_logs VALUES (261, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 20:43:57');
INSERT INTO public.audit_logs VALUES (262, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transit)', '2026-09-17 21:09:00');
INSERT INTO public.audit_logs VALUES (263, 1, 'System Settings', 'Updated system branding identity (name: Palompon 
Transit)', '2026-09-17 21:09:13');
INSERT INTO public.audit_logs VALUES (264, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:10:22');
INSERT INTO public.audit_logs VALUES (265, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:10:43');
INSERT INTO public.audit_logs VALUES (266, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:12:22');
INSERT INTO public.audit_logs VALUES (267, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:12:33');
INSERT INTO public.audit_logs VALUES (268, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:12:47');
INSERT INTO public.audit_logs VALUES (269, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:12:57');
INSERT INTO public.audit_logs VALUES (270, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:13:03');
INSERT INTO public.audit_logs VALUES (271, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:13:14');
INSERT INTO public.audit_logs VALUES (272, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:13:44');
INSERT INTO public.audit_logs VALUES (273, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:46:09');
INSERT INTO public.audit_logs VALUES (274, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:47:29');
INSERT INTO public.audit_logs VALUES (275, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:47:57');
INSERT INTO public.audit_logs VALUES (276, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:48:12');
INSERT INTO public.audit_logs VALUES (277, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:48:17');
INSERT INTO public.audit_logs VALUES (278, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:53:31');
INSERT INTO public.audit_logs VALUES (279, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:53:36');
INSERT INTO public.audit_logs VALUES (280, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:53:43');
INSERT INTO public.audit_logs VALUES (281, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:53:47');
INSERT INTO public.audit_logs VALUES (282, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:54:06');
INSERT INTO public.audit_logs VALUES (283, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:54:21');
INSERT INTO public.audit_logs VALUES (284, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:54:23');
INSERT INTO public.audit_logs VALUES (285, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:54:51');
INSERT INTO public.audit_logs VALUES (286, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:56:20');
INSERT INTO public.audit_logs VALUES (287, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:57:56');
INSERT INTO public.audit_logs VALUES (288, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:58:30');
INSERT INTO public.audit_logs VALUES (289, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 21:58:45');
INSERT INTO public.audit_logs VALUES (290, 1, 'System Settings', 'Uploaded new system logo: 
logo_1789653817_f216e435.jpg', '2026-09-17 22:03:37');
INSERT INTO public.audit_logs VALUES (291, 1, 'System Settings', 'Reset system logo to default', '2026-09-17 
22:03:59');
INSERT INTO public.audit_logs VALUES (292, 1, 'System Settings', 'Uploaded new system logo: 
logo_1789654390_a3f4a406.png', '2026-09-17 22:13:10');
INSERT INTO public.audit_logs VALUES (293, 1, 'System Settings', 'Reset system logo to default', '2026-09-17 
22:13:23');
INSERT INTO public.audit_logs VALUES (294, 1, 'System Settings', 'Uploaded new system logo: 
logo_1789654412_feabe59c.jpg', '2026-09-17 22:13:32');
INSERT INTO public.audit_logs VALUES (295, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-17 22:16:51');
INSERT INTO public.audit_logs VALUES (296, 1, 'System Settings', 'Uploaded new system background picture: 
bg_1789654617_0cfd7835.jpg', '2026-09-17 22:16:57');
INSERT INTO public.audit_logs VALUES (297, 1, 'System Settings', 'Reset system background to default', '2026-09-17 
22:17:20');
INSERT INTO public.audit_logs VALUES (298, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-17 22:17:59');
INSERT INTO public.audit_logs VALUES (299, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-17 22:18:01');
INSERT INTO public.audit_logs VALUES (300, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-17 22:18:01');
INSERT INTO public.audit_logs VALUES (301, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-17 22:18:02');
INSERT INTO public.audit_logs VALUES (302, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 22:21:18');
INSERT INTO public.audit_logs VALUES (303, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-17 22:23:55');
INSERT INTO public.audit_logs VALUES (304, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-17 22:24:02');
INSERT INTO public.audit_logs VALUES (305, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-17 22:24:32');
INSERT INTO public.audit_logs VALUES (306, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-17 22:41:50');
INSERT INTO public.audit_logs VALUES (307, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-17 22:41:59');
INSERT INTO public.audit_logs VALUES (308, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-17 22:41:59');
INSERT INTO public.audit_logs VALUES (309, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-17 22:42:00');
INSERT INTO public.audit_logs VALUES (310, 1, 'System Settings', 'Uploaded new system background picture: 
bg_1789656125_aaa0e922.jpg', '2026-09-17 22:42:05');
INSERT INTO public.audit_logs VALUES (311, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-17 22:42:26');
INSERT INTO public.audit_logs VALUES (312, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-17 22:42:27');
INSERT INTO public.audit_logs VALUES (313, 1, 'System Settings', 'Reset system background to default', '2026-09-17 
22:42:31');
INSERT INTO public.audit_logs VALUES (314, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-17 22:43:41');
INSERT INTO public.audit_logs VALUES (315, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-17 22:43:43');
INSERT INTO public.audit_logs VALUES (316, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-17 22:43:54');
INSERT INTO public.audit_logs VALUES (317, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-17 22:43:55');
INSERT INTO public.audit_logs VALUES (318, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-17 22:43:56');
INSERT INTO public.audit_logs VALUES (319, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-17 22:43:57');
INSERT INTO public.audit_logs VALUES (320, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-18 06:50:36');
INSERT INTO public.audit_logs VALUES (321, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
06:50:47');
INSERT INTO public.audit_logs VALUES (322, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-18 06:50:53');
INSERT INTO public.audit_logs VALUES (323, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 06:53:03');
INSERT INTO public.audit_logs VALUES (324, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 06:54:06');
INSERT INTO public.audit_logs VALUES (325, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
07:20:36');
INSERT INTO public.audit_logs VALUES (326, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
08:49:50');
INSERT INTO public.audit_logs VALUES (327, 1, 'Logout', 'Super_admin arclast988@gmail.com logged out', '2026-09-18 
08:50:04');
INSERT INTO public.audit_logs VALUES (328, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
08:50:57');
INSERT INTO public.audit_logs VALUES (329, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
09:27:23');
INSERT INTO public.audit_logs VALUES (330, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-18 11:26:50');
INSERT INTO public.audit_logs VALUES (331, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-18 11:26:57');
INSERT INTO public.audit_logs VALUES (332, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 11:27:38');
INSERT INTO public.audit_logs VALUES (333, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 11:28:05');
INSERT INTO public.audit_logs VALUES (334, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 11:28:38');
INSERT INTO public.audit_logs VALUES (335, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 11:29:58');
INSERT INTO public.audit_logs VALUES (336, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 11:30:06');
INSERT INTO public.audit_logs VALUES (337, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-18 11:30:59');
INSERT INTO public.audit_logs VALUES (338, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-18 11:30:59');
INSERT INTO public.audit_logs VALUES (339, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-18 11:31:00');
INSERT INTO public.audit_logs VALUES (340, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-18 11:31:00');
INSERT INTO public.audit_logs VALUES (341, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-18 11:31:00');
INSERT INTO public.audit_logs VALUES (342, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-18 11:31:01');
INSERT INTO public.audit_logs VALUES (343, 1, 'System Settings', 'Reset system logo to default', '2026-09-18 
11:31:10');
INSERT INTO public.audit_logs VALUES (344, 1, 'System Settings', 'Reset all slideshow background pictures to 
defaults', '2026-09-18 11:31:16');
INSERT INTO public.audit_logs VALUES (345, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-18 11:38:18');
INSERT INTO public.audit_logs VALUES (346, 1, 'System Settings', 'Uploaded new system background picture: 
bg_1789702707_91471dfb.jpg', '2026-09-18 11:38:27');
INSERT INTO public.audit_logs VALUES (347, 1, 'System Settings', 'Reset system background to default', '2026-09-18 
11:38:36');
INSERT INTO public.audit_logs VALUES (348, 1, 'System Settings', 'Uploaded new system background picture: 
bg_1789702722_5a005425.png', '2026-09-18 11:38:42');
INSERT INTO public.audit_logs VALUES (349, 1, 'System Settings', 'Reset system background to default', '2026-09-18 
11:38:48');
INSERT INTO public.audit_logs VALUES (350, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-18 11:39:16');
INSERT INTO public.audit_logs VALUES (351, 1, 'Assign vehicle to route', 'Registered vehicle 999 999 (minibus) - 
Operator: 324234 - Driver: ahhah - Route: PALOMPON → ORMOC', '2026-09-18 11:47:30');
INSERT INTO public.audit_logs VALUES (352, 1, 'Reassign vehicle route', 'Reassigned 23232321 from PALOMPON → TACLOBAN 
to PALOMPON → ORMOC', '2026-09-18 11:47:53');
INSERT INTO public.audit_logs VALUES (353, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-18 12:01:24');
INSERT INTO public.audit_logs VALUES (354, 3, 'Depart Vehicle', 'Depart Vehicle for 2321232 (ORMOC). (Operator: 
JKDJFLKDJF, Driver: fddfd)', '2026-09-18 12:05:00');
INSERT INTO public.audit_logs VALUES (355, 3, 'Add to queue', 'Added 23232321 to queue for ORMOC. (Operator: 324234, 
Driver: 1232a). Rule: Afternoon (40 min).', '2026-09-18 12:05:12');
INSERT INTO public.audit_logs VALUES (356, 3, 'Cancel Trip', 'Cancel Trip for 23232321 (ORMOC). (Operator: 324234, 
Driver: 1232a)', '2026-09-18 12:05:17');
INSERT INTO public.audit_logs VALUES (357, 3, 'Add to queue', 'Added 23232321 to queue for ORMOC. (Operator: 324234, 
Driver: 1232a). Rule: Afternoon (40 min).', '2026-09-18 12:05:46');
INSERT INTO public.audit_logs VALUES (358, 3, 'Cancel Trip', 'Cancel Trip for 23232321 (ORMOC). (Operator: 324234, 
Driver: 1232a)', '2026-09-18 12:05:48');
INSERT INTO public.audit_logs VALUES (359, 3, 'Undo Cancel Trip', 'Restored trip for 23232321 back to the active 
queue.', '2026-09-18 12:05:51');
INSERT INTO public.audit_logs VALUES (360, 3, 'Cancel Trip', 'Cancel Trip for 23232321 (ORMOC). (Operator: 324234, 
Driver: 1232a)', '2026-09-18 12:06:07');
INSERT INTO public.audit_logs VALUES (361, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-18 12:17:36');
INSERT INTO public.audit_logs VALUES (362, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-18 12:17:38');
INSERT INTO public.audit_logs VALUES (363, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-18 12:17:44');
INSERT INTO public.audit_logs VALUES (364, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-18 12:17:45');
INSERT INTO public.audit_logs VALUES (365, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-18 12:17:51');
INSERT INTO public.audit_logs VALUES (366, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-18 12:17:51');
INSERT INTO public.audit_logs VALUES (367, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-18 12:17:52');
INSERT INTO public.audit_logs VALUES (368, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-18 12:17:56');
INSERT INTO public.audit_logs VALUES (369, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 12:20:20');
INSERT INTO public.audit_logs VALUES (370, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 12:20:48');
INSERT INTO public.audit_logs VALUES (371, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 12:22:05');
INSERT INTO public.audit_logs VALUES (372, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 13:06:09');
INSERT INTO public.audit_logs VALUES (373, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-18 13:06:26');
INSERT INTO public.audit_logs VALUES (374, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-18 13:06:30');
INSERT INTO public.audit_logs VALUES (375, 1, 'System Settings', 'Saved background display mode to: slideshow', 
'2026-09-18 13:06:34');
INSERT INTO public.audit_logs VALUES (376, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-18 13:06:44');
INSERT INTO public.audit_logs VALUES (377, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-18 13:06:46');
INSERT INTO public.audit_logs VALUES (378, 1, 'System Settings', 'Switched background display mode to: single', 
'2026-09-18 13:06:47');
INSERT INTO public.audit_logs VALUES (379, 1, 'System Settings', 'Switched background display mode to: slideshow', 
'2026-09-18 13:06:48');
INSERT INTO public.audit_logs VALUES (380, 1, 'System Settings', 'Saved background display mode to: single', 
'2026-09-18 13:18:03');
INSERT INTO public.audit_logs VALUES (381, 1, 'System Settings', 'Saved background display mode to: slideshow', 
'2026-09-18 13:18:05');
INSERT INTO public.audit_logs VALUES (382, 1, 'System Settings', 'Saved background display mode to: slideshow', 
'2026-09-18 13:18:39');
INSERT INTO public.audit_logs VALUES (383, 1, 'System Settings', 'Saved background display mode to: single', 
'2026-09-18 13:28:40');
INSERT INTO public.audit_logs VALUES (384, 1, 'System Settings', 'Saved background display mode to: slideshow', 
'2026-09-18 13:28:42');
INSERT INTO public.audit_logs VALUES (385, 3, 'Add to queue', 'Added 112EF to queue for ORMOC. (Operator: LETRANSCO, 
Driver: dfdfd). Rule: Afternoon (40 min).', '2026-09-18 14:30:25');
INSERT INTO public.audit_logs VALUES (386, 3, 'Start Boarding', 'Start Boarding for 112EF (ORMOC). (Operator: 
LETRANSCO, Driver: dfdfd)', '2026-09-18 14:35:28');
INSERT INTO public.audit_logs VALUES (387, 3, 'Start Boarding', 'Start Boarding for 112EF (ORMOC). (Operator: 
LETRANSCO, Driver: dfdfd)', '2026-09-18 14:35:29');
INSERT INTO public.audit_logs VALUES (388, 3, 'Start Boarding', 'Start Boarding for 112EF (ORMOC). (Operator: 
LETRANSCO, Driver: dfdfd)', '2026-09-18 14:35:29');
INSERT INTO public.audit_logs VALUES (389, 3, 'Cancel Trip', 'Cancel Trip for 112EF (ORMOC). (Operator: LETRANSCO, 
Driver: dfdfd)', '2026-09-18 14:35:36');
INSERT INTO public.audit_logs VALUES (390, 3, 'Undo Cancel Trip', 'Restored trip for 112EF back to the active queue.', 
'2026-09-18 14:35:37');
INSERT INTO public.audit_logs VALUES (391, 3, 'Start Boarding', 'Start Boarding for 112EF (ORMOC). (Operator: 
LETRANSCO, Driver: dfdfd)', '2026-09-18 14:35:38');
INSERT INTO public.audit_logs VALUES (392, 3, 'Start Boarding', 'Start Boarding for 112EF (ORMOC). (Operator: 
LETRANSCO, Driver: dfdfd)', '2026-09-18 14:35:38');
INSERT INTO public.audit_logs VALUES (393, 3, 'Start Boarding', 'Start Boarding for 112EF (ORMOC). (Operator: 
LETRANSCO, Driver: dfdfd)', '2026-09-18 14:35:39');
INSERT INTO public.audit_logs VALUES (394, 1, 'Update vehicle', 'Updated vehicle 112EF', '2026-09-18 14:36:23');
INSERT INTO public.audit_logs VALUES (395, 1, 'Update vehicle', 'Updated vehicle 112EF', '2026-09-18 14:36:30');
INSERT INTO public.audit_logs VALUES (396, 3, 'Depart Vehicle', 'Depart Vehicle for 112EF (ORMOC). (Operator: 
LETRANSCO, Driver: dfdfd)', '2026-09-18 14:37:05');
INSERT INTO public.audit_logs VALUES (397, 3, 'Add to queue', 'Added 999 999 to queue for ORMOC. (Operator: 324234, 
Driver: ahhah). Rule: Afternoon (40 min).', '2026-09-18 14:37:08');
INSERT INTO public.audit_logs VALUES (398, 3, 'Start Boarding', 'Start Boarding for 999 999 (ORMOC). (Operator: 
324234, Driver: ahhah)', '2026-09-18 14:37:09');
INSERT INTO public.audit_logs VALUES (399, 3, 'Start Boarding', 'Start Boarding for 999 999 (ORMOC). (Operator: 
324234, Driver: ahhah)', '2026-09-18 14:37:09');
INSERT INTO public.audit_logs VALUES (400, 3, 'Start Boarding', 'Start Boarding for 999 999 (ORMOC). (Operator: 
324234, Driver: ahhah)', '2026-09-18 14:37:09');
INSERT INTO public.audit_logs VALUES (401, 1, 'System Settings', 'Uploaded new system logo: 
logo_1789714721_6d5fe353.png', '2026-09-18 14:58:41');
INSERT INTO public.audit_logs VALUES (402, 1, 'System Settings', 'Uploaded new system background picture: 
bg_1789714762_9b900c51.png', '2026-09-18 14:59:22');
INSERT INTO public.audit_logs VALUES (403, 1, 'System Settings', 'Uploaded new login page hero illustration: 
login_card_1789714775_c75fc15f.png', '2026-09-18 14:59:35');
INSERT INTO public.audit_logs VALUES (404, 1, 'System Settings', 'Reset system background to default', '2026-09-18 
14:59:39');
INSERT INTO public.audit_logs VALUES (405, 1, 'System Settings', 'Saved background display mode to: slideshow', 
'2026-09-18 14:59:43');
INSERT INTO public.audit_logs VALUES (406, 1, 'System Settings', 'Deleted slideshow slot 3. Active slots remaining: 
1,2,4,5', '2026-09-18 15:04:01');
INSERT INTO public.audit_logs VALUES (407, 1, 'System Settings', 'Deleted slideshow slot 2. Active slots remaining: 
1,4,5', '2026-09-18 15:04:03');
INSERT INTO public.audit_logs VALUES (408, 1, 'System Settings', 'Deleted slideshow slot 1. Active slots remaining: 
4,5', '2026-09-18 15:04:05');
INSERT INTO public.audit_logs VALUES (409, 1, 'System Settings', 'Deleted slideshow slot 4. Active slots remaining: 
5', '2026-09-18 15:04:08');
INSERT INTO public.audit_logs VALUES (410, 1, 'System Settings', 'Saved background display mode to: slideshow', 
'2026-09-18 15:04:15');
INSERT INTO public.audit_logs VALUES (411, 1, 'System Settings', 'Added new slideshow slot 6: 
bg_slot_6_1789715113_1b53e68c.png', '2026-09-18 15:05:13');
INSERT INTO public.audit_logs VALUES (412, 1, 'System Settings', 'Deleted slideshow slot 5. Active slots remaining: 
6', '2026-09-18 15:05:30');
INSERT INTO public.audit_logs VALUES (413, 1, 'System Settings', 'Added new slideshow slot 7: 
bg_slot_7_1789715140_5d8cf99e.png', '2026-09-18 15:05:40');
INSERT INTO public.audit_logs VALUES (414, 1, 'System Settings', 'Saved background display mode to: slideshow', 
'2026-09-18 15:05:45');
INSERT INTO public.audit_logs VALUES (415, 1, 'System Settings', 'Reset all slideshow background pictures to 
defaults', '2026-09-18 15:58:18');
INSERT INTO public.audit_logs VALUES (416, 1, 'System Settings', 'Reset system logo to default', '2026-09-18 
15:58:48');
INSERT INTO public.audit_logs VALUES (417, 1, 'System Settings', 'Saved background display mode to: slideshow', 
'2026-09-18 16:07:47');
INSERT INTO public.audit_logs VALUES (418, 1, 'System Settings', 'Reset login page hero illustration to default 
artwork', '2026-09-18 16:08:36');
INSERT INTO public.audit_logs VALUES (419, 1, 'System Settings', 'Reset all slideshow background pictures to 
defaults', '2026-09-18 16:08:48');
INSERT INTO public.audit_logs VALUES (420, 1, 'System Settings', 'Saved background display mode to: single', 
'2026-09-18 16:14:14');
INSERT INTO public.audit_logs VALUES (421, 1, 'System Settings', 'Saved background display mode to: slideshow', 
'2026-09-18 16:14:21');
INSERT INTO public.audit_logs VALUES (422, 3, 'Add to queue', 'Added 23232321 to queue for ORMOC. (Operator: 324234, 
Driver: 1232a). Rule: Afternoon Rush (30 min).', '2026-09-18 17:57:07');
INSERT INTO public.audit_logs VALUES (423, 3, 'Add to queue', 'Added 2321232 to queue for ORMOC. (Operator: 
JKDJFLKDJF, Driver: fddfd). Rule: Afternoon Rush (30 min).', '2026-09-18 17:57:12');
INSERT INTO public.audit_logs VALUES (424, 1, 'Deactivate vehicle', 'Deactivated vehicle 999 999.', '2026-09-18 
18:00:42');
INSERT INTO public.audit_logs VALUES (425, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 19:21:10');
INSERT INTO public.audit_logs VALUES (426, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 19:21:19');
INSERT INTO public.audit_logs VALUES (427, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 19:21:29');
INSERT INTO public.audit_logs VALUES (428, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 19:21:37');
INSERT INTO public.audit_logs VALUES (429, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 19:21:59');
INSERT INTO public.audit_logs VALUES (430, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 19:22:05');
INSERT INTO public.audit_logs VALUES (431, 3, 'Add to queue', 'Added 112EF to queue for ORMOC. (Operator: LETRANSCO, 
Driver: dfdfd). Rule: Evening (40 min).', '2026-09-18 19:29:03');
INSERT INTO public.audit_logs VALUES (432, 3, 'Cancel Trip', 'Cancel Trip for 23232321 (ORMOC). (Operator: 324234, 
Driver: 1232a)', '2026-09-18 19:29:08');
INSERT INTO public.audit_logs VALUES (433, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-18 19:29:11');
INSERT INTO public.audit_logs VALUES (434, 3, 'Cancel Trip', 'Cancel Trip for 112EF (ORMOC). (Operator: LETRANSCO, 
Driver: dfdfd)', '2026-09-18 19:29:12');
INSERT INTO public.audit_logs VALUES (435, 3, 'Add to queue', 'Added 23232321 to queue for ORMOC. (Operator: 324234, 
Driver: 1232a). Rule: Evening (40 min).', '2026-09-18 19:29:17');
INSERT INTO public.audit_logs VALUES (436, 3, 'Add to queue', 'Added 2321232 to queue for ORMOC. (Operator: 
JKDJFLKDJF, Driver: fddfd). Rule: Evening (40 min).', '2026-09-18 19:29:17');
INSERT INTO public.audit_logs VALUES (437, 3, 'Add to queue', 'Added 112EF to queue for ORMOC. (Operator: LETRANSCO, 
Driver: dfdfd). Rule: Evening (40 min).', '2026-09-18 19:29:17');
INSERT INTO public.audit_logs VALUES (438, 3, 'Cancel Trip', 'Cancel Trip for 23232321 (ORMOC). (Operator: 324234, 
Driver: 1232a)', '2026-09-18 19:29:23');
INSERT INTO public.audit_logs VALUES (439, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-18 19:29:25');
INSERT INTO public.audit_logs VALUES (440, 3, 'Cancel Trip', 'Cancel Trip for 112EF (ORMOC). (Operator: LETRANSCO, 
Driver: dfdfd)', '2026-09-18 19:29:28');
INSERT INTO public.audit_logs VALUES (441, 3, 'Undo Cancel Trip', 'Restored trip for 112EF back to the active queue.', 
'2026-09-18 19:29:29');
INSERT INTO public.audit_logs VALUES (442, 3, 'Cancel Trip', 'Cancel Trip for 112EF (ORMOC). (Operator: LETRANSCO, 
Driver: dfdfd)', '2026-09-18 19:29:33');
INSERT INTO public.audit_logs VALUES (443, 3, 'Add to queue', 'Added 23232321 to queue for ORMOC. (Operator: 324234, 
Driver: 1232a). Rule: Evening (40 min).', '2026-09-18 19:31:17');
INSERT INTO public.audit_logs VALUES (444, 3, 'Cancel Trip', 'Cancel Trip for 23232321 (ORMOC). (Operator: 324234, 
Driver: 1232a)', '2026-09-18 19:31:19');
INSERT INTO public.audit_logs VALUES (445, 1, 'Assign vehicle to route', 'Registered vehicle HELO123 (minibus) - 
Operator: 324234 - Driver: fddfd - Route: PALOMPON → ORMOC', '2026-09-18 19:32:00');
INSERT INTO public.audit_logs VALUES (446, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
19:38:52');
INSERT INTO public.audit_logs VALUES (447, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-18 19:41:57');
INSERT INTO public.audit_logs VALUES (448, 3, 'Add to queue', 'Added 23232321 to queue for ORMOC. (Operator: 324234, 
Driver: 1232a). Rule: Evening (40 min).', '2026-09-18 19:42:03');
INSERT INTO public.audit_logs VALUES (449, 3, 'Add to queue', 'Added HELO123 to queue for ORMOC. (Operator: 324234, 
Driver: fddfd). Rule: Evening (40 min).', '2026-09-18 19:42:03');
INSERT INTO public.audit_logs VALUES (450, 3, 'Add to queue', 'Added 2321232 to queue for ORMOC. (Operator: 
JKDJFLKDJF, Driver: fddfd). Rule: Evening (40 min).', '2026-09-18 19:42:03');
INSERT INTO public.audit_logs VALUES (451, 3, 'Add to queue', 'Added 112EF to queue for ORMOC. (Operator: LETRANSCO, 
Driver: dfdfd). Rule: Evening (40 min).', '2026-09-18 19:42:03');
INSERT INTO public.audit_logs VALUES (452, 3, 'Cancel Trip', 'Cancel Trip for 23232321 (ORMOC). (Operator: 324234, 
Driver: 1232a)', '2026-09-18 19:42:08');
INSERT INTO public.audit_logs VALUES (453, 3, 'Add to queue', 'Added 23232321 to queue for ORMOC. (Operator: 324234, 
Driver: 1232a). Rule: Evening (40 min).', '2026-09-18 19:42:10');
INSERT INTO public.audit_logs VALUES (454, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-18 19:42:53');
INSERT INTO public.audit_logs VALUES (455, 3, 'Cancel Trip', 'Cancel Trip for HELO123 (ORMOC). (Operator: 324234, 
Driver: fddfd)', '2026-09-18 19:42:57');
INSERT INTO public.audit_logs VALUES (456, 3, 'Add to queue', 'Added HELO123 to queue for ORMOC. (Operator: 324234, 
Driver: fddfd). Rule: Evening (40 min).', '2026-09-18 19:43:00');
INSERT INTO public.audit_logs VALUES (457, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-18 19:43:08');
INSERT INTO public.audit_logs VALUES (458, 3, 'Undo Cancel Trip', 'Restored trip for 2321232 back to the active 
queue.', '2026-09-18 19:43:09');
INSERT INTO public.audit_logs VALUES (459, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-18 19:43:13');
INSERT INTO public.audit_logs VALUES (460, 3, 'Undo Cancel Trip', 'Restored trip for 2321232 back to the active 
queue.', '2026-09-18 19:43:14');
INSERT INTO public.audit_logs VALUES (461, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-18 19:43:16');
INSERT INTO public.audit_logs VALUES (462, 3, 'Undo Cancel Trip', 'Restored trip for 2321232 back to the active 
queue.', '2026-09-18 19:43:17');
INSERT INTO public.audit_logs VALUES (463, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-18 19:43:19');
INSERT INTO public.audit_logs VALUES (464, 3, 'Undo Cancel Trip', 'Restored trip for 2321232 back to the active 
queue.', '2026-09-18 19:43:20');
INSERT INTO public.audit_logs VALUES (465, 3, 'Start Boarding', 'Start Boarding for 2321232 (ORMOC). (Operator: 
JKDJFLKDJF, Driver: fddfd)', '2026-09-18 19:43:22');
INSERT INTO public.audit_logs VALUES (466, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-18 19:43:26');
INSERT INTO public.audit_logs VALUES (467, 3, 'Add to queue', 'Added 2321232 to queue for ORMOC. (Operator: 
JKDJFLKDJF, Driver: fddfd). Rule: Evening (40 min).', '2026-09-18 19:43:28');
INSERT INTO public.audit_logs VALUES (468, 3, 'Start Boarding', 'Start Boarding for 112EF (ORMOC). (Operator: 
LETRANSCO, Driver: dfdfd)', '2026-09-18 19:43:35');
INSERT INTO public.audit_logs VALUES (469, 3, 'Cancel Trip', 'Cancel Trip for 112EF (ORMOC). (Operator: LETRANSCO, 
Driver: dfdfd)', '2026-09-18 19:43:37');
INSERT INTO public.audit_logs VALUES (470, 3, 'Add to queue', 'Added 112EF to queue for ORMOC. (Operator: LETRANSCO, 
Driver: dfdfd). Rule: Evening (40 min).', '2026-09-18 19:43:41');
INSERT INTO public.audit_logs VALUES (471, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
19:44:05');
INSERT INTO public.audit_logs VALUES (472, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 19:44:16');
INSERT INTO public.audit_logs VALUES (473, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 19:44:34');
INSERT INTO public.audit_logs VALUES (474, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 19:44:45');
INSERT INTO public.audit_logs VALUES (475, 3, 'Cancel Trip', 'Cancel Trip for 23232321 (ORMOC). (Operator: 324234, 
Driver: 1232a)', '2026-09-18 19:54:55');
INSERT INTO public.audit_logs VALUES (476, 3, 'Add to queue', 'Added 23232321 to queue for ORMOC. (Operator: 324234, 
Driver: 1232a). Rule: Evening (40 min).', '2026-09-18 19:54:59');
INSERT INTO public.audit_logs VALUES (477, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
20:01:54');
INSERT INTO public.audit_logs VALUES (478, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-18 20:02:11');
INSERT INTO public.audit_logs VALUES (479, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 20:02:19');
INSERT INTO public.audit_logs VALUES (480, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 20:02:33');
INSERT INTO public.audit_logs VALUES (481, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 20:02:44');
INSERT INTO public.audit_logs VALUES (482, 3, 'Cancel Trip', 'Cancel Trip for HELO123 (ORMOC). (Operator: 324234, 
Driver: fddfd)', '2026-09-18 20:04:44');
INSERT INTO public.audit_logs VALUES (483, 3, 'Add to queue', 'Added HELO123 to queue for ORMOC. (Operator: 324234, 
Driver: fddfd). Rule: Evening (40 min).', '2026-09-18 20:04:47');
INSERT INTO public.audit_logs VALUES (484, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-18 20:10:26');
INSERT INTO public.audit_logs VALUES (485, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
20:10:45');
INSERT INTO public.audit_logs VALUES (486, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 20:10:57');
INSERT INTO public.audit_logs VALUES (487, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 20:11:18');
INSERT INTO public.audit_logs VALUES (488, 1, 'Update vehicle type', 'Updated vehicle type Jeepney to Jeepney.', 
'2026-09-18 20:13:13');
INSERT INTO public.audit_logs VALUES (489, 1, 'Update vehicle type', 'Updated vehicle type Jeepney to Jeepney.', 
'2026-09-18 20:13:22');
INSERT INTO public.audit_logs VALUES (490, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-18 20:13:34');
INSERT INTO public.audit_logs VALUES (491, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-18 20:15:38');
INSERT INTO public.audit_logs VALUES (492, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-18 20:15:50');
INSERT INTO public.audit_logs VALUES (493, 1, 'Update vehicle type', 'Updated vehicle type Minibus to Minibus.', 
'2026-09-18 20:16:11');
INSERT INTO public.audit_logs VALUES (494, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
20:21:06');
INSERT INTO public.audit_logs VALUES (495, 1, 'Logout', 'Super_admin arclast988@gmail.com logged out', '2026-09-18 
20:21:16');
INSERT INTO public.audit_logs VALUES (496, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
20:21:23');
INSERT INTO public.audit_logs VALUES (497, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 20:21:31');
INSERT INTO public.audit_logs VALUES (498, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-18 20:21:53');
INSERT INTO public.audit_logs VALUES (499, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-18 20:21:58');
INSERT INTO public.audit_logs VALUES (500, 3, 'Add to queue', 'Added 2321232 to queue for ORMOC. (Operator: 
JKDJFLKDJF, Driver: fddfd). Rule: Evening (40 min).', '2026-09-18 20:22:00');
INSERT INTO public.audit_logs VALUES (501, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 20:22:09');
INSERT INTO public.audit_logs VALUES (502, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
20:31:00');
INSERT INTO public.audit_logs VALUES (503, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-18 20:31:10');
INSERT INTO public.audit_logs VALUES (504, 3, 'Cancel Trip', 'Cancel Trip for 112EF (ORMOC). (Operator: LETRANSCO, 
Driver: dfdfd)', '2026-09-18 20:31:15');
INSERT INTO public.audit_logs VALUES (505, 3, 'Add to queue', 'Added 112EF to queue for ORMOC. (Operator: LETRANSCO, 
Driver: dfdfd). Rule: Evening (40 min).', '2026-09-18 20:31:18');
INSERT INTO public.audit_logs VALUES (506, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 20:31:37');
INSERT INTO public.audit_logs VALUES (507, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
20:42:57');
INSERT INTO public.audit_logs VALUES (508, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-18 20:43:12');
INSERT INTO public.audit_logs VALUES (509, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
20:49:44');
INSERT INTO public.audit_logs VALUES (510, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-18 20:49:54');
INSERT INTO public.audit_logs VALUES (511, 3, 'Cancel Trip', 'Cancel Trip for 23232321 (ORMOC). (Operator: 324234, 
Driver: 1232a)', '2026-09-18 20:49:58');
INSERT INTO public.audit_logs VALUES (512, 3, 'Cancel Trip', 'Cancel Trip for HELO123 (ORMOC). (Operator: 324234, 
Driver: fddfd)', '2026-09-18 20:50:00');
INSERT INTO public.audit_logs VALUES (513, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-18 20:50:03');
INSERT INTO public.audit_logs VALUES (514, 3, 'Cancel Trip', 'Cancel Trip for 112EF (ORMOC). (Operator: LETRANSCO, 
Driver: dfdfd)', '2026-09-18 20:50:05');
INSERT INTO public.audit_logs VALUES (515, 3, 'Add to queue', 'Added 23232321 to queue for ORMOC. (Operator: 324234, 
Driver: 1232a). Rule: Evening (40 min).', '2026-09-18 20:50:09');
INSERT INTO public.audit_logs VALUES (516, 3, 'Add to queue', 'Added HELO123 to queue for ORMOC. (Operator: 324234, 
Driver: fddfd). Rule: Evening (40 min).', '2026-09-18 20:50:09');
INSERT INTO public.audit_logs VALUES (517, 3, 'Add to queue', 'Added 2321232 to queue for ORMOC. (Operator: 
JKDJFLKDJF, Driver: fddfd). Rule: Evening (40 min).', '2026-09-18 20:50:09');
INSERT INTO public.audit_logs VALUES (518, 3, 'Add to queue', 'Added 112EF to queue for ORMOC. (Operator: LETRANSCO, 
Driver: dfdfd). Rule: Evening (40 min).', '2026-09-18 20:50:09');
INSERT INTO public.audit_logs VALUES (519, 3, 'Start Boarding', 'Start Boarding for 23232321 (ORMOC). (Operator: 
324234, Driver: 1232a)', '2026-09-18 20:50:19');
INSERT INTO public.audit_logs VALUES (520, 3, 'Start Boarding', 'Start Boarding for HELO123 (ORMOC). (Operator: 
324234, Driver: fddfd)', '2026-09-18 20:50:22');
INSERT INTO public.audit_logs VALUES (521, 3, 'Start Boarding', 'Start Boarding for 112EF (ORMOC). (Operator: 
LETRANSCO, Driver: dfdfd)', '2026-09-18 20:50:24');
INSERT INTO public.audit_logs VALUES (522, 3, 'Cancel Trip', 'Cancel Trip for HELO123 (ORMOC). (Operator: 324234, 
Driver: fddfd)', '2026-09-18 20:50:28');
INSERT INTO public.audit_logs VALUES (523, 3, 'Cancel Trip', 'Cancel Trip for 112EF (ORMOC). (Operator: LETRANSCO, 
Driver: dfdfd)', '2026-09-18 20:50:29');
INSERT INTO public.audit_logs VALUES (524, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-18 20:50:31');
INSERT INTO public.audit_logs VALUES (525, 3, 'Cancel Trip', 'Cancel Trip for 23232321 (ORMOC). (Operator: 324234, 
Driver: 1232a)', '2026-09-18 20:50:32');
INSERT INTO public.audit_logs VALUES (526, 3, 'Add to queue', 'Added 23232321 to queue for ORMOC. (Operator: 324234, 
Driver: 1232a). Rule: Evening (40 min).', '2026-09-18 20:50:35');
INSERT INTO public.audit_logs VALUES (527, 3, 'Add to queue', 'Added HELO123 to queue for ORMOC. (Operator: 324234, 
Driver: fddfd). Rule: Evening (40 min).', '2026-09-18 20:50:35');
INSERT INTO public.audit_logs VALUES (528, 3, 'Add to queue', 'Added 2321232 to queue for ORMOC. (Operator: 
JKDJFLKDJF, Driver: fddfd). Rule: Evening (40 min).', '2026-09-18 20:50:35');
INSERT INTO public.audit_logs VALUES (529, 3, 'Add to queue', 'Added 112EF to queue for ORMOC. (Operator: LETRANSCO, 
Driver: dfdfd). Rule: Evening (40 min).', '2026-09-18 20:50:35');
INSERT INTO public.audit_logs VALUES (530, 3, 'Start Boarding', 'Start Boarding for 23232321 (ORMOC). (Operator: 
324234, Driver: 1232a)', '2026-09-18 20:51:02');
INSERT INTO public.audit_logs VALUES (531, 3, 'Start Boarding', 'Start Boarding for 2321232 (ORMOC). (Operator: 
JKDJFLKDJF, Driver: fddfd)', '2026-09-18 20:51:05');
INSERT INTO public.audit_logs VALUES (532, 3, 'Start Boarding', 'Start Boarding for 112EF (ORMOC). (Operator: 
LETRANSCO, Driver: dfdfd)', '2026-09-18 20:51:10');
INSERT INTO public.audit_logs VALUES (533, 3, 'Cancel Trip', 'Cancel Trip for 112EF (ORMOC). (Operator: LETRANSCO, 
Driver: dfdfd)', '2026-09-18 20:51:13');
INSERT INTO public.audit_logs VALUES (534, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-18 20:51:15');
INSERT INTO public.audit_logs VALUES (535, 3, 'Cancel Trip', 'Cancel Trip for 23232321 (ORMOC). (Operator: 324234, 
Driver: 1232a)', '2026-09-18 20:51:17');
INSERT INTO public.audit_logs VALUES (536, 3, 'Cancel Trip', 'Cancel Trip for HELO123 (ORMOC). (Operator: 324234, 
Driver: fddfd)', '2026-09-18 20:51:20');
INSERT INTO public.audit_logs VALUES (537, 3, 'Add to queue', 'Added 23232321 to queue for ORMOC. (Operator: 324234, 
Driver: 1232a). Rule: Evening (40 min).', '2026-09-18 20:51:24');
INSERT INTO public.audit_logs VALUES (538, 3, 'Add to queue', 'Added HELO123 to queue for ORMOC. (Operator: 324234, 
Driver: fddfd). Rule: Evening (40 min).', '2026-09-18 20:51:24');
INSERT INTO public.audit_logs VALUES (539, 3, 'Add to queue', 'Added 2321232 to queue for ORMOC. (Operator: 
JKDJFLKDJF, Driver: fddfd). Rule: Evening (40 min).', '2026-09-18 20:51:24');
INSERT INTO public.audit_logs VALUES (540, 3, 'Add to queue', 'Added 112EF to queue for ORMOC. (Operator: LETRANSCO, 
Driver: dfdfd). Rule: Evening (40 min).', '2026-09-18 20:51:24');
INSERT INTO public.audit_logs VALUES (541, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 20:52:51');
INSERT INTO public.audit_logs VALUES (542, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 20:52:57');
INSERT INTO public.audit_logs VALUES (543, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 20:53:04');
INSERT INTO public.audit_logs VALUES (544, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-18 21:08:57');
INSERT INTO public.audit_logs VALUES (545, 3, 'Start Boarding', 'Start Boarding for 23232321 (ORMOC). (Operator: 
324234, Driver: 1232a)', '2026-09-18 21:10:24');
INSERT INTO public.audit_logs VALUES (546, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-18 21:14:19');
INSERT INTO public.audit_logs VALUES (547, 3, 'Start Boarding', 'Start Boarding for 112EF (ORMOC). (Operator: 
LETRANSCO, Driver: dfdfd)', '2026-09-18 21:14:28');
INSERT INTO public.audit_logs VALUES (548, 3, 'Start Boarding', 'Start Boarding for 2321232 (ORMOC). (Operator: 
JKDJFLKDJF, Driver: fddfd)', '2026-09-18 21:14:33');
INSERT INTO public.audit_logs VALUES (549, 3, 'Start Boarding', 'Start Boarding for HELO123 (ORMOC). (Operator: 
324234, Driver: fddfd)', '2026-09-18 21:14:37');
INSERT INTO public.audit_logs VALUES (550, 3, 'Cancel Trip', 'Cancel Trip for 23232321 (ORMOC). (Operator: 324234, 
Driver: 1232a)', '2026-09-18 21:14:46');
INSERT INTO public.audit_logs VALUES (551, 3, 'Cancel Trip', 'Cancel Trip for HELO123 (ORMOC). (Operator: 324234, 
Driver: fddfd)', '2026-09-18 21:14:47');
INSERT INTO public.audit_logs VALUES (552, 3, 'Cancel Trip', 'Cancel Trip for 2321232 (ORMOC). (Operator: JKDJFLKDJF, 
Driver: fddfd)', '2026-09-18 21:14:49');
INSERT INTO public.audit_logs VALUES (553, 3, 'Cancel Trip', 'Cancel Trip for 112EF (ORMOC). (Operator: LETRANSCO, 
Driver: dfdfd)', '2026-09-18 21:14:51');
INSERT INTO public.audit_logs VALUES (554, 3, 'Add to queue', 'Added 23232321 to queue for ORMOC. (Operator: 324234, 
Driver: 1232a). Rule: Late Night (60 min).', '2026-09-18 21:14:54');
INSERT INTO public.audit_logs VALUES (555, 3, 'Add to queue', 'Added HELO123 to queue for ORMOC. (Operator: 324234, 
Driver: fddfd). Rule: Late Night (60 min).', '2026-09-18 21:14:54');
INSERT INTO public.audit_logs VALUES (556, 3, 'Add to queue', 'Added 2321232 to queue for ORMOC. (Operator: 
JKDJFLKDJF, Driver: fddfd). Rule: Late Night (60 min).', '2026-09-18 21:14:54');
INSERT INTO public.audit_logs VALUES (557, 3, 'Add to queue', 'Added 112EF to queue for ORMOC. (Operator: LETRANSCO, 
Driver: dfdfd). Rule: Late Night (60 min).', '2026-09-18 21:14:55');
INSERT INTO public.audit_logs VALUES (558, 3, 'Start Boarding', 'Start Boarding for 112EF (ORMOC). (Operator: 
LETRANSCO, Driver: dfdfd)', '2026-09-18 21:14:57');
INSERT INTO public.audit_logs VALUES (559, 3, 'Start Boarding', 'Start Boarding for HELO123 (ORMOC). (Operator: 
324234, Driver: fddfd)', '2026-09-18 21:23:41');
INSERT INTO public.audit_logs VALUES (560, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
21:25:43');
INSERT INTO public.audit_logs VALUES (561, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-18 21:25:51');
INSERT INTO public.audit_logs VALUES (562, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-18 21:39:18');
INSERT INTO public.audit_logs VALUES (563, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
21:40:59');
INSERT INTO public.audit_logs VALUES (564, 3, 'Login', 'Staff logged in (jycgrac@gmail.com)', '2026-09-18 21:58:37');
INSERT INTO public.audit_logs VALUES (565, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-18 
22:03:31');
INSERT INTO public.audit_logs VALUES (566, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-19 
07:43:33');
INSERT INTO public.audit_logs VALUES (567, 1, 'Logout', 'Super_admin arclast988@gmail.com logged out', '2026-09-19 
07:43:40');
INSERT INTO public.audit_logs VALUES (568, 1, 'Login', 'Super_admin logged in (arclast988@gmail.com)', '2026-09-19 
07:43:46');
INSERT INTO public.audit_logs VALUES (569, 1, 'System Settings', 'Updated role theme colours (9 settings)', 
'2026-09-19 07:43:52');
INSERT INTO public.audit_logs VALUES (570, 1, 'System Settings', 'Updated system themes & identity (name: Terminal 
Queue)', '2026-09-19 07:47:17');
INSERT INTO public.audit_logs VALUES (571, 1, 'System Settings', 'Updated footer and public information', '2026-09-19 
07:48:24');
INSERT INTO public.audit_logs VALUES (572, 1, 'System Settings', 'Updated system themes & identity (name: Terminal 
Queue)', '2026-09-19 07:48:59');
INSERT INTO public.audit_logs VALUES (573, 2, 'Login', 'Admin logged in (noynayjaylo@gmail.com)', '2026-09-19 
07:51:18');


--
-- Data for Name: departure_rules; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.departure_rules VALUES (1, '00:00:00', '05:00:00', 60, 'Late Night / Early Morning', NULL, NULL, 1, 
NULL);
INSERT INTO public.departure_rules VALUES (2, '05:00:00', '09:00:00', 30, 'Morning Rush', NULL, NULL, 1, NULL);
INSERT INTO public.departure_rules VALUES (3, '09:00:00', '12:00:00', 40, 'Mid-Morning', NULL, NULL, 1, NULL);
INSERT INTO public.departure_rules VALUES (4, '12:00:00', '15:00:00', 40, 'Afternoon', NULL, NULL, 1, NULL);
INSERT INTO public.departure_rules VALUES (5, '15:00:00', '18:00:00', 30, 'Afternoon Rush', NULL, NULL, 1, NULL);
INSERT INTO public.departure_rules VALUES (6, '18:00:00', '21:00:00', 40, 'Evening', NULL, NULL, 1, NULL);
INSERT INTO public.departure_rules VALUES (7, '21:00:00', '23:59:59', 60, 'Late Night', NULL, NULL, 1, NULL);


--
-- Data for Name: fare_discounts; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.fare_discounts VALUES (1, 'pwd', 'PWD Discount', 20.00, 1, NULL, NULL, 1);
INSERT INTO public.fare_discounts VALUES (3, 'student', 'Student Discount', 15.00, 1, NULL, NULL, 1);
INSERT INTO public.fare_discounts VALUES (4, 'regular', 'Regular Fare', 0.00, 1, NULL, NULL, 1);
INSERT INTO public.fare_discounts VALUES (2, 'senior_citizen', 'Senior Citizen Discount', 20.00, 1, NULL, '2026-09-04 
13:36:10', 1);


--
-- Data for Name: fares; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.fares VALUES (29, 4, 1, 364.00, '2026-09-03 19:23:17', '2026-09-03 19:23:17');
INSERT INTO public.fares VALUES (30, 4, 3, 386.75, '2026-09-03 19:23:17', '2026-09-03 19:23:17');
INSERT INTO public.fares VALUES (31, 4, 4, 455.00, '2026-09-03 19:23:17', '2026-09-03 19:23:17');
INSERT INTO public.fares VALUES (33, 5, 1, 34.40, '2026-09-03 19:23:17', '2026-09-03 19:23:17');
INSERT INTO public.fares VALUES (34, 5, 3, 36.55, '2026-09-03 19:23:17', '2026-09-03 19:23:17');
INSERT INTO public.fares VALUES (35, 5, 4, 43.00, '2026-09-03 19:23:17', '2026-09-03 19:23:17');
INSERT INTO public.fares VALUES (32, 4, 2, 364.00, '2026-09-03 19:23:17', '2026-09-04 13:36:10');
INSERT INTO public.fares VALUES (36, 5, 2, 34.40, '2026-09-03 19:23:17', '2026-09-04 13:36:10');
INSERT INTO public.fares VALUES (53, 2, 1, 40.00, '2026-09-04 14:49:19', '2026-09-04 14:49:19');
INSERT INTO public.fares VALUES (54, 2, 3, 42.50, '2026-09-04 14:49:19', '2026-09-04 14:49:19');
INSERT INTO public.fares VALUES (55, 2, 4, 50.00, '2026-09-04 14:49:19', '2026-09-04 14:49:19');
INSERT INTO public.fares VALUES (56, 2, 2, 40.00, '2026-09-04 14:49:19', '2026-09-04 14:49:19');


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.migrations VALUES (1, '2026-02-03-144119', 'App\Database\Migrations\InitialSchema', 'default', 
'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (2, '2026-02-06-103530', 'App\Database\Migrations\AddCurrentPassengersToQueue', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (3, '2026-02-06-104947', 'App\Database\Migrations\AddVehicleTypeToRoutes', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (4, '2026-02-06-121011', 'App\Database\Migrations\AddDefaultRouteToVehicles', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (5, '2026-02-08-100000', 'App\Database\Migrations\CreateAnnouncementsTable', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (6, '2026-03-24-131600', 
'App\Database\Migrations\RemoveOperatorSimplifyVehicles', 'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (7, '2026-05-07-100000', 'App\Database\Migrations\AddEstimatedDepartureToQueue', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (8, '2026-05-07-100100', 'App\Database\Migrations\CreateDepartureRulesTable', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (9, '2026-05-07-100200', 'App\Database\Migrations\CreateFareDiscountsTable', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (10, '2026-05-07-100300', 'App\Database\Migrations\CleanupUsersRoleEnum', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (11, '2026-05-07-100400', 'App\Database\Migrations\AddQueueIndexes', 'default', 
'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (12, '2026-05-15-150000', 'App\Database\Migrations\MoveRouteFareToFaresTable', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (13, '2026-05-15-180000', 'App\Database\Migrations\MergeFaresBackIntoRoutes', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (14, '2026-05-16-000000', 'App\Database\Migrations\DropTripStatusHistoryTable', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (15, '2026-05-16-100000', 'App\Database\Migrations\CreateUserRoutesTable', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (16, '2026-05-22-200000', 'App\Database\Migrations\RedesignFaresAndConfigs', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (17, '2026-05-22-201000', 
'App\Database\Migrations\HardenFareRedesignTerminalIndexes', 'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (18, '2026-05-22-202000', 
'App\Database\Migrations\NormalizeRouteOriginsFromTerminals', 'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (19, '2026-05-22-203000', 'App\Database\Migrations\RemoveRouteOriginUseTerminal', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (20, '2026-05-23-000000', 'App\Database\Migrations\RenameLogsToAuditLogs', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (21, '2026-05-31-000000', 'App\Database\Migrations\AddRouteIdToDepartureRules', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (22, '2026-06-16-000000', 'App\Database\Migrations\AddLoginRateLimitToUsers', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (23, '2026-06-16-100000', 
'App\Database\Migrations\CreatePasswordResetTokensTable', 'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (24, '2026-06-17-000000', 
'App\Database\Migrations\AddResetCodeToPasswordResetTokens', 'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (25, '2026-06-17-000001', 'App\Database\Migrations\AddEmailToUsers', 'default', 
'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (26, '2026-06-17-000002', 
'App\Database\Migrations\AddCodeAttemptsToPasswordResetTokens', 'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (27, '2026-06-20-100000', 'App\Database\Migrations\AddSuperAdminRole', 'default', 
'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (28, '2026-08-09-190000', 'App\Database\Migrations\CreateVehicleTypes', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (29, '2026-08-13-200000', 'App\Database\Migrations\AddOperatorNameToVehicles', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (30, '2026-08-20-220000', 'App\Database\Migrations\AddSnapshotFieldsToQueue', 
'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (31, '2026-08-25-220000', 
'App\Database\Migrations\AddPerformanceCompositeIndexes', 'default', 'App', 1788421662, 1);
INSERT INTO public.migrations VALUES (32, '2026-09-03-190500', 
'App\Database\Migrations\AddColorAndIconToVehicleTypes', 'default', 'App', 1788433575, 2);
INSERT INTO public.migrations VALUES (33, '2026-09-03-191000', 
'App\Database\Migrations\AddUserIdToPasswordResetTokens', 'default', 'App', 1788436007, 3);
INSERT INTO public.migrations VALUES (34, '2026-09-04-120000', 'App\Database\Migrations\AddMissingPerformanceIndexes', 
'default', 'App', 1788495448, 4);
INSERT INTO public.migrations VALUES (35, '2026-09-10-100000', 'App\Database\Migrations\AddProfileImageToUsers', 
'default', 'App', 1789510004, 5);
INSERT INTO public.migrations VALUES (36, '2026-09-11-100000', 'App\Database\Migrations\AddStatusToUsers', 'default', 
'App', 1789510004, 5);
INSERT INTO public.migrations VALUES (37, '2026-09-11-110000', 'App\Database\Migrations\AddStatusToRoutes', 'default', 
'App', 1789510004, 5);
INSERT INTO public.migrations VALUES (38, '2026-09-12-091500', 'App\Database\Migrations\AddSeverityToAnnouncements', 
'default', 'App', 1789510004, 5);
INSERT INTO public.migrations VALUES (39, '2026-09-15-160000', 'App\Database\Migrations\CreateSystemSettingsTable', 
'default', 'App', 1789510004, 5);
INSERT INTO public.migrations VALUES (40, '2026-09-16-170000', 'App\Database\Migrations\AddPhotoToVehicleTypes', 
'default', 'App', 1789548651, 6);
INSERT INTO public.migrations VALUES (41, '2026-09-17-060000', 'App\Database\Migrations\AddPhotoToVehicles', 
'default', 'App', 1789594805, 7);
INSERT INTO public.migrations VALUES (42, '2026-09-17-220000', 
'App\Database\Migrations\UpdateVehicleTypeIconsAndColors', 'default', 'App', 1789654262, 8);


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.password_reset_tokens VALUES (1, 'arclast988@gmail.com', 
'dc2c22259c35f9118ea34dba3e16908d6e9ef7603304039b4284357006860d47', '2026-09-03 19:57:08', 0, '2026-09-03 19:47:08', 
'767636', 'arclast988@gmail.com', 0, 0, 1);
INSERT INTO public.password_reset_tokens VALUES (2, 'noynayjaylo@gmail.com', 
'17031467e1cc81d04ff60ea78ed88f2730a71b5512e904d4c77d9909e7dbd504', '2026-09-18 07:24:34', 1, '2026-09-18 07:14:34', 
'651066', 'noynayjaylo@gmail.com', 0, 0, 2);
INSERT INTO public.password_reset_tokens VALUES (3, 'noynayjaylo@gmail.com', 
'5293c006a82b2914475db11cdf2fce18755061c067424a90a2346a5229fda032', '2026-09-18 07:27:25', 1, '2026-09-18 07:17:25', 
'469007', 'noynayjaylo@gmail.com', 0, 0, 2);
INSERT INTO public.password_reset_tokens VALUES (4, 'noynayjaylo@gmail.com', 
'6fd113174da10b5637f55907c482567b093816d75ed96b38f584eacd98b4b1b2', '2026-09-18 09:00:15', 0, '2026-09-18 08:50:15', 
'681386', 'noynayjaylo@gmail.com', 0, 0, 2);
INSERT INTO public.password_reset_tokens VALUES (5, 'jycgrac@gmail.com', 
'05c4e919b2eb68e4cc54cd596e5b7278439d2aa98767fed0f95d16915d63e61b', '2026-09-18 19:28:13', 0, '2026-09-18 19:18:13', 
'196910', 'jycgrac@gmail.com', 0, 0, 3);


--
-- Data for Name: queue; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.queue VALUES (3, 10, 2, 'canceled', 0, 0, '2026-09-03 19:25:42', NULL, NULL, 'fddfd', 'JKDjflkdjf', 
'2321232');
INSERT INTO public.queue VALUES (13, 10, 2, 'departed', 12, 0, '2026-09-04 12:21:13', '2026-09-04 12:29:28', 
'2026-09-04 13:06:45', 'fddfd', 'JKDjflkdjf', '2321232');
INSERT INTO public.queue VALUES (6, 10, 2, 'canceled', 0, 0, '2026-09-03 19:26:14', NULL, NULL, 'fddfd', 'JKDjflkdjf', 
'2321232');
INSERT INTO public.queue VALUES (11, 10, 2, 'departed', 0, 0, '2026-09-03 19:27:37', '2026-09-03 19:29:23', 
'2026-09-03 20:09:20', 'fddfd', 'JKDjflkdjf', '2321232');
INSERT INTO public.queue VALUES (33, 10, 2, 'canceled', 0, 0, '2026-09-18 19:43:28', NULL, NULL, 'fddfd', 
'JKDJFLKDJF', '2321232');
INSERT INTO public.queue VALUES (25, 14, 2, 'canceled', 0, 0, '2026-09-18 19:29:19', NULL, NULL, 'dfdfd', 'LETRANSCO', 
'112EF');
INSERT INTO public.queue VALUES (26, 11, 2, 'canceled', 0, 0, '2026-09-18 19:31:17', NULL, NULL, '1232a', '324234', 
'23232321');
INSERT INTO public.queue VALUES (34, 14, 2, 'canceled', 0, 0, '2026-09-18 19:43:41', NULL, NULL, 'dfdfd', 'LETRANSCO', 
'112EF');
INSERT INTO public.queue VALUES (49, 10, 2, 'canceled', 0, 0, '2026-09-18 20:51:26', NULL, NULL, 'fddfd', 
'JKDJFLKDJF', '2321232');
INSERT INTO public.queue VALUES (27, 11, 2, 'canceled', 0, 0, '2026-09-18 19:42:03', NULL, NULL, '1232a', '324234', 
'23232321');
INSERT INTO public.queue VALUES (40, 16, 2, 'canceled', 0, 0, '2026-09-18 20:50:10', NULL, NULL, 'fddfd', '324234', 
'HELO123');
INSERT INTO public.queue VALUES (18, 14, 2, 'departed', 0, 0, '2026-09-18 14:30:25', '2026-09-18 14:37:05', 
'2026-09-18 15:15:39', 'dfdfd', 'LETRANSCO', '112EF');
INSERT INTO public.queue VALUES (42, 14, 2, 'canceled', 0, 0, '2026-09-18 20:50:12', NULL, NULL, 'dfdfd', 'LETRANSCO', 
'112EF');
INSERT INTO public.queue VALUES (41, 10, 2, 'canceled', 0, 0, '2026-09-18 20:50:11', NULL, NULL, 'fddfd', 
'JKDJFLKDJF', '2321232');
INSERT INTO public.queue VALUES (39, 11, 2, 'canceled', 0, 0, '2026-09-18 20:50:09', NULL, NULL, '1232a', '324234', 
'23232321');
INSERT INTO public.queue VALUES (35, 11, 2, 'canceled', 4, 0, '2026-09-18 19:54:59', NULL, NULL, '1232a', '324234', 
'23232321');
INSERT INTO public.queue VALUES (15, 10, 2, 'departed', 0, 0, '2026-09-17 00:58:35', '2026-09-18 12:05:00', 
'2026-09-17 09:42:07', 'fddfd', 'JKDJFLKDJF', '2321232');
INSERT INTO public.queue VALUES (28, 16, 2, 'canceled', 0, 0, '2026-09-18 19:42:04', NULL, NULL, 'fddfd', '324234', 
'HELO123');
INSERT INTO public.queue VALUES (36, 16, 2, 'canceled', 0, 0, '2026-09-18 20:04:47', NULL, NULL, 'fddfd', '324234', 
'HELO123');
INSERT INTO public.queue VALUES (16, 11, 2, 'canceled', 0, 0, '2026-09-18 12:05:11', NULL, NULL, '1232a', '324234', 
'23232321');
INSERT INTO public.queue VALUES (37, 10, 2, 'canceled', 0, 0, '2026-09-18 20:22:00', NULL, NULL, 'fddfd', 
'JKDJFLKDJF', '2321232');
INSERT INTO public.queue VALUES (19, 15, 2, 'canceled', 0, 1, '2026-09-18 14:37:08', NULL, '2026-09-18 15:17:09', 
'ahhah', '324234', '999 999');
INSERT INTO public.queue VALUES (17, 11, 2, 'canceled', 0, 0, '2026-09-18 12:05:46', NULL, NULL, '1232a', '324234', 
'23232321');
INSERT INTO public.queue VALUES (29, 10, 2, 'canceled', 0, 0, '2026-09-18 19:42:05', NULL, NULL, 'fddfd', 
'JKDJFLKDJF', '2321232');
INSERT INTO public.queue VALUES (38, 14, 2, 'canceled', 0, 0, '2026-09-18 20:31:18', NULL, NULL, 'dfdfd', 'LETRANSCO', 
'112EF');
INSERT INTO public.queue VALUES (47, 11, 2, 'canceled', 0, 0, '2026-09-18 20:51:24', NULL, NULL, '1232a', '324234', 
'23232321');
INSERT INTO public.queue VALUES (20, 11, 2, 'canceled', 0, 0, '2026-09-18 17:57:07', NULL, NULL, '1232a', '324234', 
'23232321');
INSERT INTO public.queue VALUES (31, 11, 2, 'canceled', 0, 0, '2026-09-18 19:42:10', NULL, NULL, '1232a', '324234', 
'23232321');
INSERT INTO public.queue VALUES (21, 10, 2, 'canceled', 0, 0, '2026-09-18 17:57:12', NULL, NULL, 'fddfd', 
'JKDJFLKDJF', '2321232');
INSERT INTO public.queue VALUES (48, 16, 2, 'canceled', 0, 0, '2026-09-18 20:51:25', NULL, NULL, 'fddfd', '324234', 
'HELO123');
INSERT INTO public.queue VALUES (22, 14, 2, 'canceled', 0, 0, '2026-09-18 19:29:03', NULL, NULL, 'dfdfd', 'LETRANSCO', 
'112EF');
INSERT INTO public.queue VALUES (50, 14, 2, 'canceled', 0, 0, '2026-09-18 20:51:27', NULL, NULL, 'dfdfd', 'LETRANSCO', 
'112EF');
INSERT INTO public.queue VALUES (45, 10, 2, 'canceled', 0, 0, '2026-09-18 20:50:37', NULL, NULL, 'fddfd', 
'JKDJFLKDJF', '2321232');
INSERT INTO public.queue VALUES (30, 14, 2, 'canceled', 0, 0, '2026-09-18 19:42:06', NULL, NULL, 'dfdfd', 'LETRANSCO', 
'112EF');
INSERT INTO public.queue VALUES (23, 11, 2, 'canceled', 0, 0, '2026-09-18 19:29:17', NULL, NULL, '1232a', '324234', 
'23232321');
INSERT INTO public.queue VALUES (43, 11, 2, 'canceled', 0, 0, '2026-09-18 20:50:35', NULL, NULL, '1232a', '324234', 
'23232321');
INSERT INTO public.queue VALUES (24, 10, 2, 'canceled', 0, 0, '2026-09-18 19:29:18', NULL, NULL, 'fddfd', 
'JKDJFLKDJF', '2321232');
INSERT INTO public.queue VALUES (44, 16, 2, 'canceled', 0, 0, '2026-09-18 20:50:36', NULL, NULL, 'fddfd', '324234', 
'HELO123');
INSERT INTO public.queue VALUES (32, 16, 2, 'canceled', 0, 0, '2026-09-18 19:43:00', NULL, NULL, 'fddfd', '324234', 
'HELO123');
INSERT INTO public.queue VALUES (52, 16, 2, 'boarding', 0, 1, '2026-09-18 21:14:55', NULL, '2026-09-18 22:23:41', 
'fddfd', '324234', 'HELO123');
INSERT INTO public.queue VALUES (54, 14, 2, 'boarding', 0, 2, '2026-09-18 21:14:57', NULL, '2026-09-18 23:23:41', 
'dfdfd', 'LETRANSCO', '112EF');
INSERT INTO public.queue VALUES (51, 11, 2, 'waiting', 0, 3, '2026-09-18 21:14:54', NULL, '2026-09-19 00:23:41', 
'1232a', '324234', '23232321');
INSERT INTO public.queue VALUES (53, 10, 2, 'waiting', 0, 4, '2026-09-18 21:14:56', NULL, '2026-09-19 01:23:41', 
'fddfd', 'JKDJFLKDJF', '2321232');
INSERT INTO public.queue VALUES (46, 14, 2, 'canceled', 0, 0, '2026-09-18 20:50:38', NULL, NULL, 'dfdfd', 'LETRANSCO', 
'112EF');


--
-- Data for Name: routes; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.routes VALUES (4, 'TACLOBAN', 'jeepney', 1, '2026-09-03 18:18:03', 'active');
INSERT INTO public.routes VALUES (5, 'TACLOBAN', 'minibus', 1, '2026-09-03 18:18:03', 'active');
INSERT INTO public.routes VALUES (2, 'ORMOC', 'minibus', 1, '2026-09-03 16:07:29', 'active');


--
-- Data for Name: system_settings; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.system_settings VALUES (28, 'contact_address', 'Terminal Queue Monitoring System, Villaba, Leyte 
6537', '2026-09-16 06:06:44', '2026-09-19 07:48:59');
INSERT INTO public.system_settings VALUES (26, 'contact_email', 'arclast988@gmail.com', '2026-09-16 06:06:44', 
'2026-09-19 07:48:59');
INSERT INTO public.system_settings VALUES (41, 'login_headline', 'Move every van, jeepney & bus on time.', '2026-09-19 
07:47:17', '2026-09-19 07:48:59');
INSERT INTO public.system_settings VALUES (42, 'login_subheadline', 'gives dispatchers a live view of vehicle queues, 
routes, and departures — so every trip leaves the terminal on schedule.', '2026-09-19 07:47:17', '2026-09-19 
07:48:59');
INSERT INTO public.system_settings VALUES (43, 'login_kicker', 'Terminal Operations', '2026-09-19 07:47:17', 
'2026-09-19 07:48:59');
INSERT INTO public.system_settings VALUES (44, 'login_feature1_title', 'Real-time queue', '2026-09-19 07:47:17', 
'2026-09-19 07:48:59');
INSERT INTO public.system_settings VALUES (45, 'login_feature1_desc', 'Live queue and departure status across every 
route.', '2026-09-19 07:47:17', '2026-09-19 07:48:59');
INSERT INTO public.system_settings VALUES (46, 'login_feature2_title', 'Secure & audited', '2026-09-19 07:47:17', 
'2026-09-19 07:48:59');
INSERT INTO public.system_settings VALUES (47, 'login_feature2_desc', 'Role-based access with a full activity audit 
trail.', '2026-09-19 07:47:17', '2026-09-19 07:48:59');
INSERT INTO public.system_settings VALUES (29, 'vehicle_cooldown_minutes', '30', '2026-09-17 08:20:05', '2026-09-17 
08:20:05');
INSERT INTO public.system_settings VALUES (30, 'log_retention_days', '60', '2026-09-17 08:20:05', '2026-09-17 
08:20:05');
INSERT INTO public.system_settings VALUES (31, 'departure_retention_days', '60', '2026-09-17 08:20:05', '2026-09-17 
08:20:05');
INSERT INTO public.system_settings VALUES (13, 'theme_guest_primary', '#c62828', '2026-09-16 06:06:44', '2026-09-19 
07:43:52');
INSERT INTO public.system_settings VALUES (14, 'theme_guest_nav_bg', '#ffffff', '2026-09-16 06:06:44', '2026-09-19 
07:43:52');
INSERT INTO public.system_settings VALUES (15, 'theme_guest_nav_text', '#1c2430', '2026-09-16 06:06:44', '2026-09-19 
07:43:52');
INSERT INTO public.system_settings VALUES (16, 'theme_staff_primary', '#15803d', '2026-09-16 06:06:44', '2026-09-19 
07:43:52');
INSERT INTO public.system_settings VALUES (17, 'theme_staff_nav_bg', '#15803d', '2026-09-16 06:06:44', '2026-09-19 
07:43:52');
INSERT INTO public.system_settings VALUES (18, 'theme_staff_nav_text', '#ffffff', '2026-09-16 06:06:44', '2026-09-19 
07:43:52');
INSERT INTO public.system_settings VALUES (19, 'theme_admin_primary', '#b71c1c', '2026-09-16 06:06:44', '2026-09-19 
07:43:52');
INSERT INTO public.system_settings VALUES (20, 'theme_admin_nav_bg', '#b71c1c', '2026-09-16 06:06:44', '2026-09-19 
07:43:52');
INSERT INTO public.system_settings VALUES (21, 'theme_admin_nav_text', '#ffffff', '2026-09-16 06:06:44', '2026-09-19 
07:43:52');
INSERT INTO public.system_settings VALUES (6, 'app_background_image', NULL, '2026-09-16 06:06:44', '2026-09-18 
14:59:39');
INSERT INTO public.system_settings VALUES (22, 'footer_about_title', 'TQMS', '2026-09-16 06:06:44', '2026-09-19 
07:48:24');
INSERT INTO public.system_settings VALUES (23, 'footer_about_text', 'Terminal Queue Monitoring System provides 
real-time tracking of vehicle queues and departure schedules to ensure efficient travel for every passenger.', 
'2026-09-16 06:06:44', '2026-09-19 07:48:24');
INSERT INTO public.system_settings VALUES (5, 'app_logo', NULL, '2026-09-16 06:06:44', '2026-09-18 15:58:48');
INSERT INTO public.system_settings VALUES (24, 'footer_credit', 'Municipality of Villaba, Leyte', '2026-09-16 
06:06:44', '2026-09-19 07:48:24');
INSERT INTO public.system_settings VALUES (25, 'footer_copyright_text', '© {year} {title} ({acronym}). All rights 
reserved. | {credit}', '2026-09-16 06:06:44', '2026-09-19 07:48:24');
INSERT INTO public.system_settings VALUES (1, 'app_name', 'Terminal Queue', '2026-09-16 06:06:44', '2026-09-19 
07:48:59');
INSERT INTO public.system_settings VALUES (2, 'app_subtitle', 'Monitoring System', '2026-09-16 06:06:44', '2026-09-19 
07:48:59');
INSERT INTO public.system_settings VALUES (33, 'app_login_card_image', NULL, '2026-09-18 14:59:35', '2026-09-18 
16:08:36');
INSERT INTO public.system_settings VALUES (36, 'app_bg_slideshow_1', NULL, '2026-09-18 15:58:17', '2026-09-18 
16:08:48');
INSERT INTO public.system_settings VALUES (3, 'acronym', 'TQMS', '2026-09-16 06:06:44', '2026-09-19 07:48:59');
INSERT INTO public.system_settings VALUES (4, 'system_title', 'Terminal Queue Monitoring System', '2026-09-16 
06:06:44', '2026-09-19 07:48:59');
INSERT INTO public.system_settings VALUES (27, 'contact_phone', '(053) 555-8376 / 338-2022', '2026-09-16 06:06:44', 
'2026-09-19 07:48:59');
INSERT INTO public.system_settings VALUES (37, 'app_bg_slideshow_2', NULL, '2026-09-18 15:58:18', '2026-09-18 
16:08:48');
INSERT INTO public.system_settings VALUES (38, 'app_bg_slideshow_3', NULL, '2026-09-18 15:58:18', '2026-09-18 
16:08:48');
INSERT INTO public.system_settings VALUES (39, 'app_bg_slideshow_4', NULL, '2026-09-18 15:58:18', '2026-09-18 
16:08:48');
INSERT INTO public.system_settings VALUES (40, 'app_bg_slideshow_5', NULL, '2026-09-18 15:58:18', '2026-09-18 
16:08:48');
INSERT INTO public.system_settings VALUES (32, 'app_bg_slideshow_slots', '1,2,3,4,5', '2026-09-18 11:31:16', 
'2026-09-18 16:08:48');
INSERT INTO public.system_settings VALUES (7, 'app_bg_mode', 'slideshow', '2026-09-16 06:06:44', '2026-09-18 
16:14:21');


--
-- Data for Name: terminals; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.terminals VALUES (1, 'Palompon', 'Palompon, Leyte', 100, '2026-09-03 16:07:01');
INSERT INTO public.terminals VALUES (2, 'Cebu', 'Cebu, City', 100, '2026-09-04 13:10:30');


--
-- Data for Name: user_routes; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.user_routes VALUES (5, 3, 2, NULL);
INSERT INTO public.user_routes VALUES (7, 3, 4, NULL);
INSERT INTO public.user_routes VALUES (8, 3, 5, NULL);


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.users VALUES (3, 'jycgrac@gmail.com', 
'$2y$12$Fl8VBvyQa0VLVeq63s2s0OW1o7akj7vw4la36OHJ5t5zjpn2mJCOm', 'staff', 'Operator User', '2026-09-03 16:00:26', 
'2026-09-18 21:58:37', 0, NULL, 'jycgrac@gmail.com', NULL, 'active');
INSERT INTO public.users VALUES (1, 'arclast988@gmail.com', 
'$2y$12$GF/FZItYbhdL9j/3vpBGUePziMmiDLMxuG8MWzaxZbyDVojGtAzIi', 'super_admin', 'System Administrator', '2026-09-03 
16:00:26', '2026-09-19 07:43:46', 0, NULL, 'arclast988@gmail.com', NULL, 'active');
INSERT INTO public.users VALUES (2, 'noynayjaylo@gmail.com', 
'$2y$12$NPwLInVnj3aGOf1g0fXDaOkbgGQkt7laErk2utP2zY.qiDYJBta0G', 'admin', 'Staff User', '2026-09-03 16:00:26', 
'2026-09-19 07:51:18', 0, NULL, 'noynayjaylo@gmail.com', NULL, 'active');


--
-- Data for Name: vehicle_types; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.vehicle_types VALUES (7, 'Tricecle', 'tricecle', 1, '2026-09-17 05:53:32', '2026-09-17 05:53:32', 
'#7c3aed', 'fa-motorcycle', NULL);
INSERT INTO public.vehicle_types VALUES (1, 'Van', 'van', 1, NULL, '2026-09-04 12:59:51', '#c62828', 'fa-van-shuttle', 
NULL);
INSERT INTO public.vehicle_types VALUES (5, 'Bus', 'bus', 1, '2026-09-17 05:52:52', '2026-09-17 05:52:52', '#ea580c', 
'fa-bus-simple', NULL);
INSERT INTO public.vehicle_types VALUES (6, 'Taxi', 'taxi', 1, '2026-09-17 05:53:13', '2026-09-17 05:53:13', 
'#ca8a04', 'fa-taxi', NULL);
INSERT INTO public.vehicle_types VALUES (2, 'Jeepney', 'jeepney', 1, NULL, '2026-09-18 20:13:22', '#1565c0', 
'fa-truck-front', NULL);
INSERT INTO public.vehicle_types VALUES (3, 'Minibus', 'minibus', 1, NULL, '2026-09-18 20:16:11', '#2e7d32', 'fa-bus', 
NULL);


--
-- Data for Name: vehicles; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.vehicles VALUES (10, '2321232', 'minibus', 2, 12, 'JKDJFLKDJF', NULL, 'active', '2026-09-03 
19:22:02.300042', 'fddfd', 'JKDJFLKDJF', NULL);
INSERT INTO public.vehicles VALUES (11, '23232321', 'minibus', 2, 12, '324234', NULL, 'active', '2026-09-03 
19:22:02.300042', '1232a', '324234', NULL);
INSERT INTO public.vehicles VALUES (14, '112EF', 'minibus', 2, 14, 'LETRANSCO', NULL, 'active', '2026-09-17 09:08:31', 
'dfdfd', 'LETRANSCO', NULL);
INSERT INTO public.vehicles VALUES (15, '999 999', 'minibus', 2, 12, '324234', NULL, 'archived', '2026-09-18 
11:47:30', 'ahhah', '324234', NULL);
INSERT INTO public.vehicles VALUES (16, 'HELO123', 'minibus', 2, 12, '324234', NULL, 'active', '2026-09-18 19:32:00', 
'fddfd', '324234', NULL);


--
-- Name: announcements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.announcements_id_seq', 2, true);


--
-- Name: audit_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.audit_logs_id_seq', 573, true);


--
-- Name: departure_rules_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.departure_rules_id_seq', 7, true);


--
-- Name: fare_discounts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.fare_discounts_id_seq', 4, true);


--
-- Name: fares_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.fares_id_seq', 60, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 42, true);


--
-- Name: password_reset_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.password_reset_tokens_id_seq', 5, true);


--
-- Name: queue_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.queue_id_seq', 54, true);


--
-- Name: routes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.routes_id_seq', 8, true);


--
-- Name: system_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.system_settings_id_seq', 47, true);


--
-- Name: terminals_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.terminals_id_seq', 2, true);


--
-- Name: user_routes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.user_routes_id_seq', 11, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 4, true);


--
-- Name: vehicle_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.vehicle_types_id_seq', 7, true);


--
-- Name: vehicles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.vehicles_id_seq', 16, true);


--
-- Name: fare_discounts fare_discounts_terminal_type_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fare_discounts
    ADD CONSTRAINT fare_discounts_terminal_type_unique UNIQUE (terminal_id, type);


--
-- Name: fares fares_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fares
    ADD CONSTRAINT fares_pkey PRIMARY KEY (id);


--
-- Name: announcements pk_announcements; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT pk_announcements PRIMARY KEY (id);


--
-- Name: audit_logs pk_audit_logs; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT pk_audit_logs PRIMARY KEY (id);


--
-- Name: departure_rules pk_departure_rules; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.departure_rules
    ADD CONSTRAINT pk_departure_rules PRIMARY KEY (id);


--
-- Name: fare_discounts pk_fare_discounts; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fare_discounts
    ADD CONSTRAINT pk_fare_discounts PRIMARY KEY (id);


--
-- Name: migrations pk_migrations; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT pk_migrations PRIMARY KEY (id);


--
-- Name: password_reset_tokens pk_password_reset_tokens; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT pk_password_reset_tokens PRIMARY KEY (id);


--
-- Name: queue pk_queue; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.queue
    ADD CONSTRAINT pk_queue PRIMARY KEY (id);


--
-- Name: routes pk_routes; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routes
    ADD CONSTRAINT pk_routes PRIMARY KEY (id);


--
-- Name: system_settings pk_system_settings; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.system_settings
    ADD CONSTRAINT pk_system_settings PRIMARY KEY (id);


--
-- Name: terminals pk_terminals; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.terminals
    ADD CONSTRAINT pk_terminals PRIMARY KEY (id);


--
-- Name: user_routes pk_user_routes; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_routes
    ADD CONSTRAINT pk_user_routes PRIMARY KEY (id);


--
-- Name: users pk_users; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT pk_users PRIMARY KEY (id);


--
-- Name: vehicle_types pk_vehicle_types; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vehicle_types
    ADD CONSTRAINT pk_vehicle_types PRIMARY KEY (id);


--
-- Name: vehicles pk_vehicles; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT pk_vehicles PRIMARY KEY (id);


--
-- Name: system_settings system_settings_setting_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.system_settings
    ADD CONSTRAINT system_settings_setting_key UNIQUE (setting_key);


--
-- Name: fares unique_route_discount; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fares
    ADD CONSTRAINT unique_route_discount UNIQUE (route_id, fare_discount_id);


--
-- Name: routes unique_terminal_destination_vehicle; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routes
    ADD CONSTRAINT unique_terminal_destination_vehicle UNIQUE (terminal_id, destination, vehicle_type);


--
-- Name: user_routes unique_user_route; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_routes
    ADD CONSTRAINT unique_user_route UNIQUE (user_id, route_id);


--
-- Name: users users_username_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: vehicle_types vehicle_types_slug; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vehicle_types
    ADD CONSTRAINT vehicle_types_slug UNIQUE (slug);


--
-- Name: vehicles vehicles_plate_number_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_plate_number_key UNIQUE (plate_number);


--
-- Name: announcements_is_active; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX announcements_is_active ON public.announcements USING btree (is_active);


--
-- Name: idx_announcements_active_sort; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_announcements_active_sort ON public.announcements USING btree (is_active, sort_order);


--
-- Name: idx_announcements_severity; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_announcements_severity ON public.announcements USING btree (severity);


--
-- Name: idx_audit_logs_action; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_audit_logs_action ON public.audit_logs USING btree (action);


--
-- Name: idx_audit_logs_timestamp; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_audit_logs_timestamp ON public.audit_logs USING btree ("timestamp");


--
-- Name: idx_departure_rules_terminal_route_time; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_departure_rules_terminal_route_time ON public.departure_rules USING btree (terminal_id, route_id, 
time_from, time_to);


--
-- Name: idx_email; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX idx_email ON public.users USING btree (email);


--
-- Name: idx_fare_discounts_terminal_active; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_fare_discounts_terminal_active ON public.fare_discounts USING btree (terminal_id, is_active);


--
-- Name: idx_fares_route_discount; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_fares_route_discount ON public.fares USING btree (route_id, fare_discount_id);


--
-- Name: idx_queue_departure_time; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_queue_departure_time ON public.queue USING btree (departure_time);


--
-- Name: idx_queue_route_status_pos; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_queue_route_status_pos ON public.queue USING btree (route_id, status, "position");


--
-- Name: idx_queue_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_queue_status ON public.queue USING btree (status);


--
-- Name: idx_queue_status_arrival; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_queue_status_arrival ON public.queue USING btree (status, arrival_time);


--
-- Name: idx_queue_status_departure; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_queue_status_departure ON public.queue USING btree (status, departure_time);


--
-- Name: idx_queue_status_position; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_queue_status_position ON public.queue USING btree (status, "position");


--
-- Name: idx_queue_vehicle_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_queue_vehicle_status ON public.queue USING btree (vehicle_id, status);


--
-- Name: idx_routes_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_routes_status ON public.routes USING btree (status);


--
-- Name: idx_routes_terminal_dest; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_routes_terminal_dest ON public.routes USING btree (terminal_id, destination);


--
-- Name: idx_user_routes_route; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_user_routes_route ON public.user_routes USING btree (route_id);


--
-- Name: idx_user_routes_user; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_user_routes_user ON public.user_routes USING btree (user_id);


--
-- Name: password_reset_tokens_token; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX password_reset_tokens_token ON public.password_reset_tokens USING btree (token);


--
-- Name: password_reset_tokens_username; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX password_reset_tokens_username ON public.password_reset_tokens USING btree (username);


--
-- Name: audit_logs audit_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON UPDATE SET NULL ON 
DELETE CASCADE;


--
-- Name: announcements fk_announcements_terminal; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT fk_announcements_terminal FOREIGN KEY (terminal_id) REFERENCES public.terminals(id) ON DELETE 
CASCADE;


--
-- Name: departure_rules fk_departure_rules_route; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.departure_rules
    ADD CONSTRAINT fk_departure_rules_route FOREIGN KEY (route_id) REFERENCES public.routes(id) ON UPDATE CASCADE ON 
DELETE SET NULL;


--
-- Name: fares fk_fares_discount; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fares
    ADD CONSTRAINT fk_fares_discount FOREIGN KEY (fare_discount_id) REFERENCES public.fare_discounts(id) ON DELETE 
CASCADE;


--
-- Name: fares fk_fares_route; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fares
    ADD CONSTRAINT fk_fares_route FOREIGN KEY (route_id) REFERENCES public.routes(id) ON DELETE CASCADE;


--
-- Name: queue queue_route_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.queue
    ADD CONSTRAINT queue_route_id_foreign FOREIGN KEY (route_id) REFERENCES public.routes(id) ON UPDATE CASCADE ON 
DELETE CASCADE;


--
-- Name: queue queue_vehicle_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.queue
    ADD CONSTRAINT queue_vehicle_id_foreign FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id) ON UPDATE CASCADE 
ON DELETE CASCADE;


--
-- Name: routes routes_terminal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routes
    ADD CONSTRAINT routes_terminal_id_foreign FOREIGN KEY (terminal_id) REFERENCES public.terminals(id) ON UPDATE 
CASCADE ON DELETE CASCADE;


--
-- Name: user_routes user_routes_route_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_routes
    ADD CONSTRAINT user_routes_route_id_foreign FOREIGN KEY (route_id) REFERENCES public.routes(id) ON UPDATE CASCADE 
ON DELETE CASCADE;


--
-- Name: user_routes user_routes_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_routes
    ADD CONSTRAINT user_routes_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON UPDATE CASCADE ON 
DELETE CASCADE;


--
-- Name: vehicles vehicles_default_route_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_default_route_id_foreign FOREIGN KEY (default_route_id) REFERENCES public.routes(id) ON 
UPDATE SET NULL ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--




SET search_path = public;

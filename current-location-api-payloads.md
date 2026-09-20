# Current user location — API request payloads (backend handoff)

This document describes how the **public frontend** sends the user's **current device location at action time** to four VizagLand APIs.

**Base URL:** `https://api.vizagland.com`

## Conventions

### Current user / device location (action-time GPS)

Captured when the user performs the action (not from `login_session_location`). Fields match the existing login/register flat convention:

| Field | Type | Required | Notes |
|-------|------|----------|--------|
| `user_latitude` | number \| null | Optional | WGS84; `null` if permission denied or unavailable |
| `user_longitude` | number \| null | Optional | |
| `user_road` | string \| null | Optional | Reverse-geocoded (Nominatim) when GPS succeeds |
| `user_colony` | string \| null | Optional | |
| `user_suburb` | string \| null | Optional | |
| `user_village` | string \| null | Optional | Geocoded place name, **not** the property search filter |
| `user_mandal` | string \| null | Optional | |
| `user_district` | string \| null | Optional | |
| `user_state` | string \| null | Optional | |
| `user_pincode` | string \| null | Optional | |
| `user_country` | string \| null | Optional | |

When reverse geocoding fails but GPS succeeds, `user_latitude` / `user_longitude` are set and address fields may be `null`.

Internal capture metadata (`accuracy`, `captured_at`) is **not** sent on these APIs unless the backend adds support later.

### Not current GPS

- **`property_*` / search filter fields** — listing geography the user is searching for.
- **`property_location[*]`** on property create — address of the **listing**, from the form.
- **`contact_enquiry_district`** — contact form business field, not GPS.

---

## 1. POST `/api/public/properties`

**Method:** POST  
**Content-Type:** `multipart/form-data`  
**Auth:** Optional — `Authorization: Bearer {token}` when logged in.

**Purpose:** Create a property listing.

### Existing structure (unchanged)

Nested business object `property_location` (listing address from form), plus other sections (`property_details`, `property_owner`, etc.).

### Current user location (updated)

Nested object **`property_posting_location`** — where the user was when they submitted the listing:

```text
property_posting_location[user_latitude]
property_posting_location[user_longitude]
property_posting_location[user_road]
property_posting_location[user_colony]
property_posting_location[user_suburb]
property_posting_location[user_village]
property_posting_location[user_mandal]
property_posting_location[user_district]
property_posting_location[user_state]
property_posting_location[user_pincode]
property_posting_location[user_country]
```

All values are captured **at submit time** for both guest and logged-in users.

### Example (representative multipart keys)

```text
property_location[property_village]=Vadapalem
property_location[property_district]=Anakapalli
…
property_posting_location[user_latitude]=17.6868
property_posting_location[user_longitude]=83.2185
property_posting_location[user_village]=Visakhapatnam
property_posting_location[user_district]=Visakhapatnam
…
property_images[]=<File>
property_documents[]=<File>
```

### Example JSON equivalent (for documentation only)

```json
{
  "property_location": {
    "property_village": "Vadapalem",
    "property_district": "Anakapalli",
    "property_mandal": "Chodavaram"
  },
  "property_posting_location": {
    "user_latitude": 17.6868,
    "user_longitude": 83.2185,
    "user_road": "Beach Road",
    "user_colony": null,
    "user_suburb": null,
    "user_village": "Visakhapatnam",
    "user_mandal": "Visakhapatnam",
    "user_district": "Visakhapatnam",
    "user_state": "Andhra Pradesh",
    "user_pincode": "530003",
    "user_country": "India"
  }
}
```

---

## 2. POST `/api/properties/search`

**Method:** POST  
**Content-Type:** `application/json`  
**Auth:** Public (no token required).

**Purpose:** Search and filter property listings.

### Existing structure (unchanged)

Filter/pagination fields from the search UI, for example:

```json
{
  "property_village": "Vadapalem",
  "property_district": "Anakapalli",
  "property_mandal": "Chodavaram",
  "listing_type": "sale",
  "sort_by": "newest",
  "page": 1,
  "limit": 12
}
```

(Omitted keys are not sent.)

### Current user location (updated)

The same **flat** `user_*` fields are added as **siblings** at the **root** of the JSON body (same convention as `/api/auth/login`), separate from `property_village` and other filters.

### Example full request body

```json
{
  "property_village": "Vadapalem",
  "property_district": "Anakapalli",
  "listing_type": "sale",
  "sort_by": "newest",
  "page": 1,
  "limit": 12,
  "user_latitude": 17.6901,
  "user_longitude": 83.2202,
  "user_road": null,
  "user_colony": null,
  "user_suburb": null,
  "user_village": "Visakhapatnam",
  "user_mandal": "Visakhapatnam",
  "user_district": "Visakhapatnam",
  "user_state": "Andhra Pradesh",
  "user_pincode": "530003",
  "user_country": "India"
}
```

### Permission denied example

```json
{
  "property_village": "Vadapalem",
  "page": 1,
  "limit": 12,
  "user_latitude": null,
  "user_longitude": null,
  "user_road": null,
  "user_colony": null,
  "user_suburb": null,
  "user_village": null,
  "user_mandal": null,
  "user_district": null,
  "user_state": null,
  "user_pincode": null,
  "user_country": null
}
```

---

## 3. POST `/api/public/property-search-history`

**Method:** POST  
**Content-Type:** `application/json`  
**Auth:** Optional — `Authorization: Bearer {token}` if the user is logged in.

**Purpose:** Record search analytics.

### Existing structure (unchanged)

```json
{
  "search_keyword": "Vadapalem",
  "search_filters": { },
  "mobile_number": "9876543210"
}
```

- **`search_filters`** — copy of **property search criteria only** (filter fields). GPS fields are **not** placed inside `search_filters`.

### Current user location (updated)

Same flat root-level `user_*` fields as search and login:

```json
{
  "search_keyword": "Vadapalem",
  "search_filters": {
    "property_village": "Vadapalem",
    "property_district": "Anakapalli",
    "page": 1,
    "limit": 12
  },
  "mobile_number": "9876543210",
  "user_latitude": 17.6901,
  "user_longitude": 83.2202,
  "user_road": null,
  "user_colony": null,
  "user_suburb": null,
  "user_village": "Visakhapatnam",
  "user_mandal": "Visakhapatnam",
  "user_district": "Visakhapatnam",
  "user_state": "Andhra Pradesh",
  "user_pincode": "530003",
  "user_country": "India"
}
```

When search and history fire within the same user action, the frontend reuses one GPS capture for both requests (short coalesce window).

---

## 4. POST `/api/public/contact-enquiries`

**Method:** POST  
**Content-Type:** `application/json`  
**Auth:** Public.

**Purpose:** Submit a contact enquiry.

### Existing structure (unchanged)

```json
{
  "contact_enquiry_full_name": "Rama Rao",
  "contact_enquiry_phone": "9876543210",
  "contact_enquiry_email": "user@example.com",
  "contact_enquiry_subject": "General enquiry",
  "contact_enquiry_property_reference_id": "",
  "contact_enquiry_district": "Visakhapatnam",
  "contact_enquiry_message": "Please call me back.",
  "contact_enquiry_consent": true
}
```

`contact_enquiry_district` remains form/business data, **not** GPS.

### Current user location (updated)

Root-level flat `user_*` fields added at submit time:

```json
{
  "contact_enquiry_full_name": "Rama Rao",
  "contact_enquiry_phone": "9876543210",
  "contact_enquiry_email": "user@example.com",
  "contact_enquiry_subject": "General enquiry",
  "contact_enquiry_property_reference_id": "",
  "contact_enquiry_district": "Visakhapatnam",
  "contact_enquiry_message": "Please call me back.",
  "contact_enquiry_consent": true,
  "user_latitude": 17.6901,
  "user_longitude": 83.2202,
  "user_road": null,
  "user_colony": null,
  "user_suburb": null,
  "user_village": "Visakhapatnam",
  "user_mandal": "Visakhapatnam",
  "user_district": "Visakhapatnam",
  "user_state": "Andhra Pradesh",
  "user_pincode": "530003",
  "user_country": "India"
}
```

---

## Backend confirmation checklist

Please confirm on the server for each endpoint:

1. Accept root-level flat `user_*` on JSON APIs (search, search-history, contact-enquiries).
2. Accept `property_posting_location[user_*]` on multipart property create.
3. Treat all location fields as **optional** when GPS is denied (nulls).
4. Ignore unknown fields if not yet implemented (forward compatibility).

If the backend prefers a nested object (e.g. `user_location: { … }`) instead of flat `user_*`, specify the exact schema and the frontend can align in a follow-up change.

---

## Frontend implementation reference

| API | Capture trigger | Merge helper |
|-----|-----------------|--------------|
| Property create | Property submit | `property_posting_location` in `buildPropertyPayload()` |
| Property search | Each search request | `mergeFlatUserLocationIntoPayload()` |
| Search history | Each history record | `mergeFlatUserLocationIntoPayload()` |
| Contact enquiry | Contact submit | `mergeFlatUserLocationIntoPayload()` |

Capture entry point: `captureCurrentActionLocation()` in `src/lib/currentActionLocation.js` (uses `locationService` geolocation + Nominatim; does **not** read `login_session_location`).

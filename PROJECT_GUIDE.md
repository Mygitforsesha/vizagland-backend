# VizagLand Backend Guide

A short handover guide for developers joining the VizagLand backend project.

## Project Stack

* Laravel 12
* Sanctum
* MySQL
* React Frontend
* GoDaddy VPS (deployment later)

## Architecture

Modules use:

* Controller
* Request
* Service
* Repository
* Resource

## Completed

### Authentication

* Login API
* Logout API
* Registration API
* Sanctum Authentication
* Role Middleware

#### Registration API

`POST /api/auth/register`

Mandatory fields:

* `user_full_name`
* `user_phone`
* `user_password`

Optional fields:

* `user_email`
* `user_membership_type`
* `user_roles`
* `user_professions`
* `user_media_sources`
* `user_social_media_sources`
* `user_other_roles`
* `user_dob`
* `user_gender`
* `user_village`
* `user_nearby_location`
* `user_custom_nearby_location`
* `user_district`
* `user_mandal`
* `user_panchayati`
* `user_gvmc_zone_ward_number`
* `user_vmrda`
* `user_registration_area`
* `user_authority`

Example payload:

```json
{
  "user_membership_type": "gold_member",
  "user_roles": ["buyer"],
  "user_professions": ["civil_engineer"],
  "user_media_sources": ["eenadu"],
  "user_social_media_sources": ["facebook", "instagram"],
  "user_other_roles": ["custom_role"],
  "user_full_name": "John Doe",
  "user_dob": "1995-08-15",
  "user_gender": "male",
  "user_phone": "9876543210",
  "user_email": "john@example.com",
  "user_village": "Madhurawada",
  "user_nearby_location": "Bus Stand",
  "user_custom_nearby_location": "Near RTC Complex",
  "user_district": "Visakhapatnam",
  "user_mandal": "Bheemunipatnam",
  "user_panchayati": "Madhurawada",
  "user_gvmc_zone_ward_number": "Zone 2 Ward 15",
  "user_vmrda": "VMRDA Area",
  "user_registration_area": "Visakhapatnam",
  "user_authority": "GVMC",
  "user_password": "Password@123"
}
```

Validation rules:

* `user_full_name` — required
* `user_phone` — required, unique
* `user_email` — nullable, unique
* `user_password` — required

Registration types (`user_roles`, `user_professions`, `user_media_sources`, `user_social_media_sources`, `user_other_roles`, `user_membership_type`) are stored in `user_registration_types`.

#### Login API

`POST /api/auth/login`

Mandatory fields (provide `user_email` or `user_phone`):

* `user_password`

Example payload (phone login):

```json
{
  "user_phone": "9876543210",
  "user_password": "Password@123"
}
```

Example payload (email login):

```json
{
  "user_email": "john@example.com",
  "user_password": "Password@123"
}
```

## Naming Standard

Every database column is prefixed with its entity name:

* Users: `user_id`, `user_full_name`, `user_phone`, `user_email`, `user_password`, `user_role`, `user_is_active`
* Properties: `property_id`, `property_title`, `property_status`
* Leads: `lead_id`, `lead_status`
* Follow-ups: `follow_up_id`, `follow_up_notes`
* Property reviews: `property_review_status`, `property_review_reviewed_by`

Laravel timestamps (`created_at`, `updated_at`) and foreign keys (`user_id`, `property_id`, `lead_id`) are unchanged.

### Property Module

* Property Create API
* Property List API
* Property Details API
* Property Update API

### Infrastructure

* GitHub Repository
* Development Branch
* Local Database Setup

## Property Workflow

Every new property is created with `property_status = draft`. The frontend cannot set status on create or update.

```
CREATE (agent / employee / public) → draft
SUBMIT FOR REVIEW (creator)        → pending_review
ADMIN APPROVE                      → approved
ADMIN REJECT                       → rejected
ADMIN REQUEST CHANGES              → draft
```

Only admin review APIs can set `approved`. Public listing shows `approved` properties only.

## Property Features

* Multiple Image Upload
* Multiple Document Upload
* Browser GPS Location
* Property Creator Tracking
* Property Source Tracking

## Pending Modules

### Property

* Media APIs
* Review APIs

### Dashboard

* Employee Dashboard
* Agent Dashboard
* Admin Dashboard

### Lead Module

* Create Lead
* Assign Lead
* Lead Listing

### Follow-up Module

* Create Follow-up
* Update Follow-up
* Follow-up Listing

### Public Website APIs

* Public Property Listing
* Property Details
* Featured Properties
* Support Request

## Important Rules

* Use table-prefixed columns
* Use Repository Pattern
* Use Service Pattern
* Use ApiResponse Trait
* Use Transactions where needed
* Do not create Master Data tables
* Dropdown values are hardcoded in React

## Future Enhancements

* Duplicate Property Matching
* Matching Percentage Engine
* Queue Processing
* Map Search
* Advanced Property Filters

## Current Status

Project is ready to continue from **Property Media APIs**.

## Before New Development

Perform a codebase audit before generating new code.

Verify:

* Routes
* Controllers
* Services
* Repositories
* Requests
* Resources
* Models
* Migrations

Classify each module as:

* Completed
* Partially Implemented
* Missing

Do not regenerate existing APIs.

Build only missing components.

Priority Order:

1. Property Media APIs
2. Property Review APIs
3. Lead Module
4. Follow-up Module
5. Dashboard APIs
6. Public Website APIs

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
* Sanctum Authentication
* Role Middleware

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

```
PUBLIC → DRAFT
AGENT → DRAFT
EMPLOYEE → DRAFT

ADMIN REVIEW →
  • APPROVED
  • REJECTED
  • REQUEST_CHANGES

APPROVED → Visible on Public Website
```

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

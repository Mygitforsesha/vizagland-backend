# Property Bulk Import Template Guide

This guide explains how to use `Property_Bulk_Import_Template.xlsx` for the future Bulk Property Import feature.

The workbook is designed for business users to download, fill in Microsoft Excel, save, and upload later. It uses friendly column names only. Internal database IDs, audit timestamps, and workflow tracking fields are intentionally excluded.

---

## Workbook Contents

| Sheet | Purpose |
|---|---|
| **Property Import** | Main sheet for entering one property per row |
| **Dropdown Values** | Allowed values for enum / controlled fields |
| **Sample Data** | One complete, import-ready example row |

---

## How to Fill the Template

1. Open `Property_Bulk_Import_Template.xlsx` in Microsoft Excel.
2. Go to the **Property Import** sheet.
3. Keep the header row as-is (do not rename columns).
4. Enter each property on a new row starting from row 2.
5. Use dropdowns wherever available (click the cell arrow).
6. Columns marked with `*` are mandatory.
7. Optionally copy the example from **Sample Data** into **Property Import** and edit it.
8. Save the file as `.xlsx`.

---

## Column Groups

Columns are arranged left to right in this order:

1. Property Information
2. Contact Information
3. Address
4. Location
5. Pricing
6. Area Details
7. Property Details
8. Construction Details
9. Amenities

---

## Mandatory Columns (`*`)

| Column | Notes |
|---|---|
| Property Title * | Short public listing title |
| Property Type * | Select from dropdown |
| Listing Type * | Sale / Rent / Lease |
| Property Source * | Admin / Employee / Agent / Public |
| Contact Name * | Primary enquiry contact |
| Contact Phone * | Prefer 10-digit Indian mobile |
| Contact Type * | Owner / Agent / Broker / Builder / Other |
| Address * | Full street address |
| Locality * | Area / colony name |
| Pincode * | Postal code |
| City * | City name |
| State * | State name |
| Price * | Numeric amount, no currency symbol |
| Area * | Numeric area value |
| Area Unit * | Sq. Ft. / Sq. M. / Acres / Hectares |

---

## Dropdown Fields

Use exact labels from the **Dropdown Values** sheet (or the in-cell dropdown).

| Field | Allowed Values |
|---|---|
| Property Type | Apartment, Villa, Independent House, Plot, Commercial, Warehouse, Farm Land |
| Listing Type | Sale, Rent, Lease |
| Ownership Type | Freehold, Leasehold, Co-operative, Power of Attorney, Other |
| Property Status | Lead, Draft, Pending Review, Request Changes, Approved, Rejected, Resolved, Published, Archived |
| Contact Type | Owner, Agent, Broker, Builder, Other |
| Property Source | Admin, Employee, Agent, Public |
| Area Unit | Sq. Ft., Sq. M., Acres, Hectares |
| Facing | East, West, North, South, North East, North West, South East, South West |
| Furnishing | Unfurnished, Semi Furnished, Fully Furnished |
| Price Range | Below 25 Lakhs … Above 5 Crore |
| Property Age | Under Construction, 0 - 1 Years, 1 - 5 Years, 5 - 10 Years, 10+ Years |
| Approval Authority | GVMC, VMRDA, Panchayat, DTCP, Other |
| Property Falls Under | GVMC, VMRDA, Panchayat, Municipality, Other |
| Construction Status | Under Construction, Ready to Move, New Launch |
| Floor Number | Ground, 1–10, 10+ |
| Is Featured / Negotiable | TRUE / FALSE |

Recommended default for new imports: **Property Status = Draft**.

---

## Media Columns

| Column | How to fill |
|---|---|
| Image File Names | Comma-separated file names or HTTPS URLs |
| Document File Names | Comma-separated file names or HTTPS URLs |

Examples:

- `apt-001-living.jpg, apt-001-bedroom.jpg`
- `https://cdn.example.com/a.jpg, https://cdn.example.com/b.jpg`
- `sale-deed.pdf, tax-receipt.pdf`

Notes for the future importer:

- Images: jpg, jpeg, png, webp
- Documents: pdf, doc, docx, zip, jpg, jpeg, png, webp
- Suggested limits: max 30 files each, max 10 MB each
- Left-to-right order is the display order
- Package local files in a zip next to the Excel, or use public URLs
- Do not embed binary files inside Excel cells

---

## Contact Extras

| Column | How to fill |
|---|---|
| Additional Contact Phones | Comma-separated phones, e.g. `9123456780, 9988776655` |
| Additional Contact Registration Types | Optional labels aligned to phones, e.g. `WhatsApp, Alternate` |

---

## Amenities

The **Amenities** column accepts a comma-separated list, for example:

`Lift, Power Backup, Security, Covered Parking, Gym`

This column is included for business completeness. Future import implementation should map it only when amenity storage is available.

---

## Fields Intentionally Excluded

Do not add these to the spreadsheet:

- Internal IDs (`property_id`, user foreign keys)
- Audit timestamps (`created_at`, `updated_at`)
- Soft-delete / deleted flags
- Workflow tracking (approved/rejected/archived/restored/resolved by/at)
- Counters (`view_count`, `lead_count`)
- Parent/copy linkage and system record metadata

These will be set by the application during import.

---

## Sample Data Sheet

The **Sample Data** sheet contains one fully completed Visakhapatnam apartment listing:

- Title: Premium 3 BHK Apartment Near MVP Colony
- Listing Type: Sale
- Property Type: Apartment
- Price: 8500000
- Contact: Sai Kumar / 9876543210
- City / Locality: Visakhapatnam / MVP Colony
- Bedrooms / Bathrooms / Parking: 3 / 3 / 2
- Facing: East
- Furnishing: Semi Furnished
- Coordinates: 17.742, 83.336

Copy that row into **Property Import** when you need a working starting point.

---

## Pre-Upload Checklist

- [ ] File saved as `.xlsx`
- [ ] Header row unchanged
- [ ] One property per row
- [ ] All `*` columns filled
- [ ] Dropdown values match allowed labels exactly
- [ ] Phone numbers are digits only (no spaces/symbols preferred)
- [ ] Price and Area are numbers (no ₹ or commas)
- [ ] Latitude between -90 and 90; Longitude between -180 and 180
- [ ] Image/document names match the media package or are valid URLs
- [ ] Email filled only when valid

---

## Future Import Mapping Notes (for implementers)

Friendly labels should map to Property enum values:

| Excel Label | Stored Value |
|---|---|
| Apartment | `apartment` |
| Villa | `villa` |
| Independent House | `independent_house` |
| Plot | `plot` |
| Commercial | `commercial` |
| Warehouse | `warehouse` |
| Farm Land | `farm_land` |
| Sale / Rent / Lease | `sale` / `rent` / `lease` |
| Sq. Ft. / Sq. M. / Acres / Hectares | `sqft` / `sqm` / `acres` / `hectares` |
| Freehold, Leasehold, Co-operative, Power of Attorney, Other | `freehold`, `leasehold`, `co_operative`, `power_of_attorney`, `other` |
| Owner, Agent, Broker, Builder, Other | `owner`, `agent`, `broker`, `builder`, `other` |
| Admin, Employee, Agent, Public | `admin`, `employee`, `agent`, `public` |

This guide and workbook are documentation/template artifacts only. They do not implement import APIs, services, or database changes.

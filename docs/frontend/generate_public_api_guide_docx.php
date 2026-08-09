<?php

/**
 * Generates Public App API Integration Guide as .docx
 */

$outPath = __DIR__.'/Public-App-Post-Property-API-Integration-Guide.docx';
$tmp = sys_get_temp_dir().'/vizagland_docx_'.uniqid();
@mkdir($tmp.'/word/_rels', 0777, true);
@mkdir($tmp.'/_rels', 0777, true);

function h(string $text): string
{
    return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function p(string $text, string $style = 'Normal', bool $bold = false): string
{
    $rPr = $bold ? '<w:rPr><w:b/><w:sz w:val="22"/></w:rPr>' : '<w:rPr><w:sz w:val="20"/></w:rPr>';
    $pPr = $style !== 'Normal'
        ? '<w:pPr><w:pStyle w:val="'.$style.'"/></w:pPr>'
        : '<w:pPr><w:spacing w:after="120"/></w:pPr>';

    return '<w:p>'.$pPr.'<w:r>'.$rPr.'<w:t xml:space="preserve">'.h($text).'</w:t></w:r></w:p>';
}

function heading(string $text, int $level): string
{
    $style = 'Heading'.$level;
    $size = $level === 1 ? '32' : ($level === 2 ? '28' : '24');

    return '<w:p><w:pPr><w:pStyle w:val="'.$style.'"/><w:spacing w:before="240" w:after="120"/></w:pPr>'
        .'<w:r><w:rPr><w:b/><w:sz w:val="'.$size.'"/></w:rPr><w:t>'.h($text).'</w:t></w:r></w:p>';
}

function bullet(string $text): string
{
    return '<w:p><w:pPr><w:ind w:left="360"/><w:spacing w:after="60"/></w:pPr>'
        .'<w:r><w:rPr><w:sz w:val="20"/></w:rPr><w:t xml:space="preserve">• '.h($text).'</w:t></w:r></w:p>';
}

function codeBlock(string $text): string
{
    $lines = explode("\n", $text);
    $xml = '';
    foreach ($lines as $line) {
        $xml .= '<w:p><w:pPr><w:shd w:val="clear" w:fill="F5F5F5"/><w:spacing w:after="0"/></w:pPr>'
            .'<w:r><w:rPr><w:rFonts w:ascii="Consolas" w:hAnsi="Consolas"/><w:sz w:val="16"/></w:rPr>'
            .'<w:t xml:space="preserve">'.h($line === '' ? ' ' : $line).'</w:t></w:r></w:p>';
    }

    return $xml;
}

function table(array $headers, array $rows): string
{
    $xml = '<w:tbl><w:tblPr><w:tblW w:w="5000" w:type="pct"/><w:tblBorders>'
        .'<w:top w:val="single" w:sz="4" w:color="CCCCCC"/>'
        .'<w:left w:val="single" w:sz="4" w:color="CCCCCC"/>'
        .'<w:bottom w:val="single" w:sz="4" w:color="CCCCCC"/>'
        .'<w:right w:val="single" w:sz="4" w:color="CCCCCC"/>'
        .'<w:insideH w:val="single" w:sz="4" w:color="CCCCCC"/>'
        .'<w:insideV w:val="single" w:sz="4" w:color="CCCCCC"/>'
        .'</w:tblBorders></w:tblPr>';

    $xml .= '<w:tr>';
    foreach ($headers as $header) {
        $xml .= '<w:tc><w:tcPr><w:shd w:val="clear" w:fill="1F4E79"/></w:tcPr>'
            .'<w:p><w:r><w:rPr><w:b/><w:color w:val="FFFFFF"/><w:sz w:val="18"/></w:rPr>'
            .'<w:t>'.h($header).'</w:t></w:r></w:p></w:tc>';
    }
    $xml .= '</w:tr>';

    foreach ($rows as $i => $row) {
        $fill = $i % 2 === 0 ? 'FFFFFF' : 'F2F2F2';
        $xml .= '<w:tr>';
        foreach ($row as $cell) {
            $xml .= '<w:tc><w:tcPr><w:shd w:val="clear" w:fill="'.$fill.'"/></w:tcPr>'
                .'<w:p><w:r><w:rPr><w:sz w:val="18"/></w:rPr><w:t xml:space="preserve">'.h((string) $cell).'</w:t></w:r></w:p></w:tc>';
        }
        $xml .= '</w:tr>';
    }

    return $xml.'</w:tbl>'.p('');
}

$body = '';
$body .= heading('VizagLand Public App — Post Property API Integration Guide', 1);
$body .= p('Document Version: 1.0');
$body .= p('Audience: Frontend Public App Team');
$body .= p('Base URL: https://api.vizagland.com/api');
$body .= p('Purpose: Render the complete Public Post Property page dynamically from backend APIs. Do not hardcode sections, fields, labels, options, or dependencies.');

$body .= heading('1. Overview', 2);
$body .= p('The Public Post Property page is driven by two configuration APIs plus one location search API:');
$body .= bullet('GET /public/property-form-config — complete form layout (sections + fields + metadata)');
$body .= bullet('GET /public/master-dropdowns — all static dropdown options in one response');
$body .= bullet('GET /master/locations/search — village / location autocomplete (existing)');
$body .= bullet('POST /public/properties — submit the property (existing create API)');

$body .= heading('2. Standard Response Envelope', 2);
$body .= p('All APIs use the same envelope.');
$body .= heading('2.1 Success', 3);
$body .= codeBlock("{\n  \"status\": \"success\",\n  \"message\": \"...\",\n  \"data\": { }\n}");
$body .= heading('2.2 Error', 3);
$body .= codeBlock("{\n  \"status\": \"error\",\n  \"message\": \"Error message\",\n  \"errors\": { }\n}");

$body .= heading('3. API 1 — Property Form Configuration', 2);
$body .= table(
    ['Property', 'Value'],
    [
        ['Endpoint', '/api/public/property-form-config'],
        ['Method', 'GET'],
        ['Authentication', 'None (Public)'],
        ['Request parameters', 'None'],
        ['Content-Type', 'application/json'],
    ]
);

$body .= heading('3.1 Purpose', 3);
$body .= p('Returns the complete Post Property form configuration so the frontend can render every section and field without hardcoding.');

$body .= heading('3.2 Response Structure', 3);
$body .= codeBlock("{\n  \"status\": \"success\",\n  \"message\": \"Property form configuration retrieved successfully.\",\n  \"data\": {\n    \"sections\": [\n      {\n        \"key\": \"property_location\",\n        \"label\": \"Property Location\",\n        \"order\": 10,\n        \"fields\": [ /* field objects */ ]\n      }\n    ]\n  }\n}");

$body .= heading('3.3 Section Order (Exact)', 3);
$body .= table(
    ['Order', 'Section Key', 'Label'],
    [
        ['10', 'property_location', 'Property Location'],
        ['20', 'property_category', 'Property Category'],
        ['30', 'property_details', 'Property Details'],
        ['40', 'property_images', 'Property Images'],
        ['50', 'property_documents', 'Property Documents'],
        ['60', 'owner_details', 'Owner Details'],
        ['70', 'other_services', 'Other Services'],
        ['80', 'property_contact_numbers', 'Property Contact Numbers'],
    ]
);
$body .= p('Render sections in the order returned by data.sections[].order. Do not reorder on the frontend.');

$body .= heading('3.4 Field Object Contract', 3);
$body .= p('Every field includes all of the following properties:');
$body .= table(
    ['Property', 'Type', 'Description'],
    [
        ['id', 'number', 'Unique field configuration ID'],
        ['key', 'string', 'Field key used for binding / submit mapping'],
        ['label', 'string', 'UI label'],
        ['placeholder', 'string|null', 'Input placeholder text'],
        ['section', 'string', 'Public section key this field belongs to'],
        ['order', 'number', 'Display order within the section'],
        ['type', 'string', 'text | number | integer | select | file | repeater'],
        ['required', 'boolean', 'Whether the field is required'],
        ['active', 'boolean', 'Whether the field is active (inactive fields are already excluded)'],
        ['readonly', 'boolean', 'If true, render as read-only'],
        ['searchable', 'boolean', 'If true, enable search/autocomplete UX'],
        ['multiple', 'boolean', 'If true, allow multiple values / files / repeater rows'],
        ['options', 'array|null', 'Inline options (usually null when options_api is used)'],
        ['options_api', 'string|null', 'API path to load options from'],
        ['validation', 'object|null', 'Extra validation metadata (e.g. repeater child fields)'],
        ['default_value', 'string|null', 'Default value if provided'],
        ['depends_on', 'object|null', 'Dependency metadata, e.g. { \"field\": \"property_category\" }'],
    ]
);

$body .= heading('3.5 Fields by Section', 3);

$body .= heading('Section 1 — Property Location (property_location)', 4);
$body .= table(
    ['Order', 'Key', 'Label', 'Type', 'Required', 'Options Source'],
    [
        ['10', 'property_village', 'Village', 'select', 'Yes', '/api/master/locations/search'],
        ['20', 'property_nearby_location', 'Nearby Location', 'select', 'No', '/api/master/locations/search'],
        ['30', 'property_custom_nearby_location', 'Custom Nearby Location', 'text', 'No', '—'],
        ['40', 'property_district', 'District', 'text', 'No', 'Auto-filled from location search'],
        ['50', 'property_mandal', 'Mandal', 'text', 'No', 'Auto-filled from location search'],
        ['60', 'property_panchayati', 'Panchayati', 'text', 'No', 'Auto-filled from location search'],
        ['70', 'property_gvmc', 'GVMC Zone / Ward', 'text', 'No', 'Auto-filled from location search'],
        ['80', 'property_vmrda', 'VMRDA', 'text', 'No', 'Auto-filled from location search'],
        ['90', 'property_registration_area', 'Registration Area', 'text', 'No', 'Auto-filled from location search'],
        ['100', 'property_authority', 'Authority', 'text', 'No', 'Auto-filled from location search'],
    ]
);

$body .= heading('Section 2 — Property Category (property_category)', 4);
$body .= table(
    ['Order', 'Key', 'Label', 'Type', 'Required', 'Options Source'],
    [
        ['10', 'property_category', 'Property Category', 'select', 'Yes', '/api/public/master-dropdowns → property_category'],
    ]
);

$body .= heading('Section 3 — Property Details (property_details)', 4);
$body .= table(
    ['Order', 'Key', 'Label', 'Type', 'Required', 'Options / Notes'],
    [
        ['10', 'property_project_name', 'Project Name', 'text', 'No', '—'],
        ['20', 'property_lp_no', 'LP Number', 'text', 'No', '—'],
        ['30', 'property_year', 'LP Year', 'select', 'No', 'master-dropdowns → lp_year'],
        ['40', 'property_total_floors', 'Total Floors', 'integer', 'No', '—'],
        ['50', 'property_block_phase', 'Block / Phase', 'text', 'No', '—'],
        ['60', 'property_flat_door_no', 'Flat / Door No', 'text', 'No', '—'],
        ['70', 'property_floor_number', 'Floor Number', 'select', 'No', 'master-dropdowns → floor_number'],
        ['80', 'property_facing', 'Facing', 'select', 'No', 'master-dropdowns → facing'],
        ['90', 'property_area', 'Area', 'number', 'Yes', '—'],
        ['100', 'property_area_unit', 'Area Unit', 'select', 'Yes', 'Depends on property_category'],
        ['110', 'property_price', 'Price', 'number', 'Yes', '—'],
        ['120', 'property_price_range', 'Price Range', 'select', 'No', 'master-dropdowns → price_range'],
        ['130', 'property_age', 'Property Age', 'select', 'No', 'master-dropdowns → property_age'],
        ['140', 'property_bedrooms', 'Bedrooms', 'select', 'No', 'master-dropdowns → bedrooms'],
        ['150', 'property_furnishing', 'Furnishing', 'select', 'No', 'master-dropdowns → furnishing'],
        ['160', 'property_under', 'Property Falls Under', 'select', 'No', 'master-dropdowns → property_falls_under'],
        ['170', 'property_approval_authority', 'Approval Authority', 'select', 'No', 'master-dropdowns → approval_authority'],
        ['180', 'property_document_no', 'Document Number', 'text', 'No', '—'],
        ['190', 'property_document_year', 'Document Year', 'select', 'No', 'master-dropdowns → document_year'],
        ['200', 'property_registration_office_area', 'Registration Office Area', 'text', 'No', '—'],
    ]
);

$body .= heading('Section 4 — Property Images (property_images)', 4);
$body .= table(
    ['Order', 'Key', 'Label', 'Type', 'Multiple'],
    [['10', 'property_images', 'Property Images', 'file', 'Yes']]
);

$body .= heading('Section 5 — Property Documents (property_documents)', 4);
$body .= table(
    ['Order', 'Key', 'Label', 'Type', 'Multiple'],
    [['10', 'property_documents', 'Property Documents', 'file', 'Yes']]
);

$body .= heading('Section 6 — Owner Details (owner_details)', 4);
$body .= table(
    ['Order', 'Key', 'Label', 'Type', 'Required'],
    [
        ['10', 'property_owner_name', 'Owner Name', 'text', 'Yes'],
        ['20', 'property_owner_phone', 'Owner Phone', 'text', 'Yes'],
    ]
);
$body .= p('Owner Email is intentionally excluded from Public Post Property configuration.');

$body .= heading('Section 7 — Other Services (other_services)', 4);
$body .= table(
    ['Order', 'Key', 'Label', 'Type'],
    [
        ['10', 'property_youtube_video_link', 'YouTube Video Link', 'text'],
        ['20', 'property_location_link', 'Google Location Link', 'text'],
    ]
);

$body .= heading('Section 8 — Property Contact Numbers (property_contact_numbers)', 4);
$body .= p('This section contains one repeater field.');
$body .= table(
    ['Order', 'Key', 'Type', 'Multiple', 'Notes'],
    [['10', 'property_contact_numbers', 'repeater', 'Yes', 'Unlimited rows']]
);
$body .= p('Repeater child fields are provided in field.validation.fields:');
$body .= table(
    ['Child Key', 'Label', 'Type', 'Required', 'Options'],
    [
        ['registration_type', 'Registration Type', 'select', 'Yes', 'master-dropdowns → registration_type'],
        ['phone_number', 'Phone Number', 'text', 'Yes', '—'],
    ]
);
$body .= p('Submit each row as: { \"registration_type\": \"owner\", \"phone_number\": \"9876543210\" }');

$body .= heading('4. API 2 — Master Dropdowns', 2);
$body .= table(
    ['Property', 'Value'],
    [
        ['Endpoint', '/api/public/master-dropdowns'],
        ['Method', 'GET'],
        ['Authentication', 'None (Public)'],
        ['Request parameters', 'None'],
    ]
);
$body .= heading('4.1 Purpose', 3);
$body .= p('Returns every static dropdown required by the Post Property page in a single API call.');
$body .= heading('4.2 Dropdown Keys in data', 3);
$body .= table(
    ['Key', 'Used By Field'],
    [
        ['registration_type', 'Contact Numbers → registration_type'],
        ['property_category', 'property_category'],
        ['facing', 'property_facing'],
        ['floor_number', 'property_floor_number'],
        ['price_range', 'property_price_range'],
        ['property_age', 'property_age'],
        ['bedrooms', 'property_bedrooms'],
        ['furnishing', 'property_furnishing'],
        ['approval_authority', 'property_approval_authority'],
        ['property_falls_under', 'property_under'],
        ['lp_year', 'property_year'],
        ['document_year', 'property_document_year'],
        ['area_units', 'All area unit values (reference list)'],
        ['category_area_units', 'Filtered Area Units by selected category'],
    ]
);
$body .= p('Each normal dropdown option object shape:');
$body .= codeBlock("{ \"value\": \"east\", \"label\": \"East\", \"order\": 10 }");

$body .= heading('5. Dependency — Property Category → Area Unit', 2);
$body .= p('Field property_area_unit includes:');
$body .= codeBlock("\"depends_on\": { \"field\": \"property_category\" }");
$body .= p('Frontend behavior:');
$body .= bullet('On page load, call GET /api/public/master-dropdowns once and cache the result.');
$body .= bullet('When the user selects property_category (example: land), read data.category_area_units.land.');
$body .= bullet('Replace Area Unit options with that array.');
$body .= bullet('If the previously selected Area Unit is not in the new list, clear it.');
$body .= bullet('When category is cleared, clear Area Unit options/value.');
$body .= p('Example mapping:');
$body .= table(
    ['Category value', 'Allowed Area Units'],
    [
        ['flats / apartment', 'SFT'],
        ['land / plot / farm_land', 'Sq.Yards, Acres, Cents'],
        ['factory / warehouse', 'SFT, Sq.Yards, Acres, Cents'],
        ['commercial', 'SFT, Sq.Yards'],
    ]
);
$body .= codeBlock("const units = masterDropdowns.category_area_units[selectedCategory] ?? [];");

$body .= heading('6. API 3 — Location Search (Existing)', 2);
$body .= table(
    ['Property', 'Value'],
    [
        ['Endpoint', '/api/master/locations/search'],
        ['Method', 'GET'],
        ['Authentication', 'None (Public)'],
        ['Query params', 'q (required), limit (optional, default 20, max 100)'],
    ]
);
$body .= p('Example: GET /api/master/locations/search?q=madhura&limit=20');
$body .= p('On village selection, auto-fill related location fields from the selected location object (district, mandal, panchayati, gvmc/ward, vmrda, registration office, authority).');
$body .= p('Location item fields include: id, village, display_label, nearby_location, district, mandal, panchayati, gvmc_zone, gvmc_ward, vmrda, registration_office, authority.');
$body .= p('Suggested mapping to form keys:');
$body .= bullet('village → property_village');
$body .= bullet('nearby_location → property_nearby_location');
$body .= bullet('district → property_district');
$body .= bullet('mandal → property_mandal');
$body .= bullet('panchayati → property_panchayati');
$body .= bullet('gvmc_zone / gvmc_ward → property_gvmc');
$body .= bullet('vmrda → property_vmrda');
$body .= bullet('registration_office → property_registration_area');
$body .= bullet('authority → property_authority');

$body .= heading('7. Sample Success Response — Form Config (Abbreviated)', 2);
$body .= codeBlock(<<<'JSON'
{
  "status": "success",
  "message": "Property form configuration retrieved successfully.",
  "data": {
    "sections": [
      {
        "key": "property_location",
        "label": "Property Location",
        "order": 10,
        "fields": [
          {
            "id": 1,
            "key": "property_village",
            "label": "Village",
            "placeholder": "Select village",
            "section": "property_location",
            "order": 10,
            "type": "select",
            "required": true,
            "active": true,
            "readonly": false,
            "searchable": true,
            "multiple": false,
            "options": null,
            "options_api": "/api/master/locations/search",
            "validation": null,
            "default_value": null,
            "depends_on": null
          }
        ]
      },
      {
        "key": "property_details",
        "label": "Property Details",
        "order": 30,
        "fields": [
          {
            "id": 20,
            "key": "property_area_unit",
            "label": "Area Unit",
            "placeholder": "Select area unit",
            "section": "property_details",
            "order": 100,
            "type": "select",
            "required": true,
            "active": true,
            "readonly": false,
            "searchable": false,
            "multiple": false,
            "options": null,
            "options_api": "/api/public/master-dropdowns",
            "validation": null,
            "default_value": null,
            "depends_on": { "field": "property_category" }
          }
        ]
      },
      {
        "key": "property_contact_numbers",
        "label": "Property Contact Numbers",
        "order": 80,
        "fields": [
          {
            "id": 40,
            "key": "property_contact_numbers",
            "label": "Property Contact Numbers",
            "type": "repeater",
            "multiple": true,
            "options_api": "/api/public/master-dropdowns",
            "validation": {
              "unlimited": true,
              "fields": [
                {
                  "key": "registration_type",
                  "label": "Registration Type",
                  "type": "select",
                  "required": true,
                  "options_api": "/api/public/master-dropdowns"
                },
                {
                  "key": "phone_number",
                  "label": "Phone Number",
                  "type": "text",
                  "required": true
                }
              ]
            }
          }
        ]
      }
    ]
  }
}
JSON);

$body .= heading('8. Sample Success Response — Master Dropdowns (Abbreviated)', 2);
$body .= codeBlock(<<<'JSON'
{
  "status": "success",
  "message": "Master dropdowns retrieved successfully.",
  "data": {
    "property_category": [
      { "value": "flats", "label": "Flats", "order": 10 },
      { "value": "land", "label": "Land", "order": 50 }
    ],
    "facing": [
      { "value": "east", "label": "East", "order": 10 }
    ],
    "category_area_units": {
      "flats": [{ "value": "sft", "label": "SFT", "order": 10 }],
      "land": [
        { "value": "sq_yards", "label": "Sq.Yards", "order": 10 },
        { "value": "acres", "label": "Acres", "order": 20 },
        { "value": "cents", "label": "Cents", "order": 30 }
      ]
    }
  }
}
JSON);

$body .= heading('9. Error Responses', 2);
$body .= table(
    ['HTTP Status', 'When', 'Example message'],
    [
        ['400 / 422', 'Validation failure on submit/search', 'Validation failed / q is required'],
        ['404', 'Wrong route', 'The route ... could not be found.'],
        ['500', 'Unexpected server error', 'Failed to ... Please try again.'],
    ]
);
$body .= p('Form config and master-dropdowns are public GET APIs and normally return 200 with status=success. Handle network failures and empty sections gracefully.');

$body .= heading('10. Frontend Integration Notes', 2);
$body .= bullet('On Post Property page mount: call property-form-config and master-dropdowns in parallel.');
$body .= bullet('Never hardcode section lists, field lists, labels, or dropdown values.');
$body .= bullet('Only render fields returned by the API (active/public fields are already filtered).');
$body .= bullet('Honor required, readonly, searchable, multiple, placeholder, and order from each field.');
$body .= bullet('If options_api is /api/public/master-dropdowns, use the cached master-dropdowns payload (do not call once per field).');
$body .= bullet('If options_api is /api/master/locations/search, call it on user search input (debounce recommended).');
$body .= bullet('Owner Email must not appear on Public Post Property.');
$body .= bullet('Contact numbers support unlimited repeater rows.');
$body .= bullet('Submit still uses existing POST /api/public/properties (multipart/form-data). Map form values into the create payload nested structure used by the create API.');
$body .= bullet('Suggested create mapping groups: property_location.*, property_details.*, property_owner.*, property_other_services.*, property_contact_numbers[], property_images[], property_documents[].');
$body .= bullet('property_category is a Public UI field. Until create accepts it directly, map it into the closest create fields as agreed with backend (do not invent new create endpoints).');

$body .= heading('11. Recommended Page Load Sequence', 2);
$body .= codeBlock(<<<'TXT'
1. GET /api/public/property-form-config
2. GET /api/public/master-dropdowns
3. Render sections/fields from (1)
4. Bind static selects from (2)
5. On village search: GET /api/master/locations/search?q=...
6. On category change: refresh Area Unit options from category_area_units
7. On submit: POST /api/public/properties
TXT);

$body .= heading('12. Contact', 2);
$body .= p('For contract changes or missing options, contact the Backend team. Do not hardcode fallback field catalogs in the Public App.');

$documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
    .'<w:body>'.$body
    .'<w:sectPr><w:pgSz w:w="12240" w:h="15840"/>'
    .'<w:pgMar w:top="720" w:right="720" w:bottom="720" w:left="720"/>'
    .'</w:sectPr></w:body></w:document>';

file_put_contents($tmp.'/[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    .'<Default Extension="xml" ContentType="application/xml"/>'
    .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
    .'<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
    .'</Types>');

file_put_contents($tmp.'/_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
    .'</Relationships>');

file_put_contents($tmp.'/word/_rels/document.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
    .'</Relationships>');

file_put_contents($tmp.'/word/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
    .'<w:style w:type="paragraph" w:styleId="Normal" w:default="1"><w:name w:val="Normal"/></w:style>'
    .'<w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/><w:qFormat/></w:style>'
    .'<w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:basedOn w:val="Normal"/><w:qFormat/></w:style>'
    .'<w:style w:type="paragraph" w:styleId="Heading3"><w:name w:val="heading 3"/><w:basedOn w:val="Normal"/><w:qFormat/></w:style>'
    .'<w:style w:type="paragraph" w:styleId="Heading4"><w:name w:val="heading 4"/><w:basedOn w:val="Normal"/><w:qFormat/></w:style>'
    .'</w:styles>');

file_put_contents($tmp.'/word/document.xml', $documentXml);

$zip = new ZipArchive();
if ($zip->open($outPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Failed to create docx\n");
    exit(1);
}

$files = [
    '[Content_Types].xml',
    '_rels/.rels',
    'word/document.xml',
    'word/styles.xml',
    'word/_rels/document.xml.rels',
];

foreach ($files as $file) {
    $zip->addFile($tmp.'/'.$file, $file);
}

$zip->close();

foreach ($files as $file) {
    @unlink($tmp.'/'.$file);
}
@rmdir($tmp.'/word/_rels');
@rmdir($tmp.'/word');
@rmdir($tmp.'/_rels');
@rmdir($tmp);

echo "Created: {$outPath}\n";

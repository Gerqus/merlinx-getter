## Using `/v5/data/travel/search`

This note is an opinionated, end-to-end guide for backend developers on how to use the Merlin Web Services (MWS) travel search endpoint.

It covers:

- obtaining and using an auth token,
- calling `/v5/data/travel/search` safely from your backend,
- configuring search conditions and views,
- understanding and post-processing the response for use with LLMs.

The guide focuses on ski-related use cases but applies to any trip search.

---

## 1. Authentication: getting and using a token

### 1.1. Get a token

**Endpoint**

- `POST https://mwsv5pro.merlinx.eu/v5/token/new`

**Request body (JSON)**

Relevant fields (see full spec for all options):

- `login` (string, required) – your MWS login.
- `password` (string, required) – your MWS password.
- `domain` (string, required unless always sent via header) – your application domain / identifier.
- `source` (string, required) – must be one of `"B2B"` or `"B2C"`.
- `type` (string, required) – usually `"web"`.
- `language` (string, optional) – default `"pl"`; controls language of descriptions.

Example minimal request:

```json
{
	"login": "your-login",
	"password": "your-password",
	"domain": "your-frontend-domain.example.com",
	"source": "B2C",
	"type": "web",
	"language": "pl"
}
```

**Response**

On success (`200`), you get:

```json
{
	"token": "base64-encoded-token-string"
}
```

Store this token **on the backend** only. Do not expose raw credentials or tokens to the LLM.

### 1.2. Send the token with each request

MWS uses an API key scheme called `TokenAuth` with dedicated headers (from the official OpenAPI spec):

- `X-TOKEN` – the token value received from `/v5/token/new`,
- `X-DOMAIN` – the application domain (same value as `domain` used when creating the token, unless encoded in the token itself).

Every call to `/v5/data/travel/search` **must** include at least `X-TOKEN`, and typically also `X-DOMAIN`:

```http
X-TOKEN: <your-token>
X-DOMAIN: your-frontend-domain.example.com
Content-Type: application/json
```

---

## 2. The `/v5/data/travel/search` endpoint

**Endpoint**

- `POST https://mwsv5pro.merlinx.eu/v5/data/travel/search`

**High-level behavior**

The search request can return up to four types of data (see official docs for precise definitions):

1. **Filters** – `unfilteredFieldValues` – available values for search fields based only on base conditions.
2. **Reduced filters** – `fieldValues` – available values after applying filter conditions.
3. **Grouped offers** – `groupedList` – grouped by a key (e.g. hotel), with basic info.
4. **Offers** – `offerList` – full offers matching all conditions.

Which of these you receive depends entirely on the `views` object in the request body.

### 2.1. Request body – core structure

The request has three main parts:

- `conditions.search` – base search conditions (what you want to search for),
- `conditions.filter` – additional filter conditions (refine / narrow; optional),
- `views` – which data sets (views) to return and how they should be configured.

In OpenAPI terms:

- `conditions.search` and `conditions.filter` both conform to `baseconditions` (plus some extras like `field_searchid`),
- `views` conforms to the `views` schema.

You **can** also provide `results.groupBy` to request grouping.

---

## 3. Minimal ski-search example

To fetch all offers that are marked as ski-region offers (`location_ski_resorts` attribute), with:

- a flat list of offers (`offerList`), and
- the same offers grouped by ski region (`skiRegionList`),

you can send:

```http
POST https://mwsv5pro.merlinx.eu/v5/data/travel/search
X-TOKEN: <your-token>
X-DOMAIN: your-frontend-domain.example.com
Content-Type: application/json
```

```json
{
	"conditions": {
		"search": {
			"Base": {},
			"Accommodation": {
				"Attributes": ["location_ski_resorts"]
			}
		},
		"filter": {}
	},
	"views": {
		"offerList": {},
		"skiRegionList": {}
	}
}
```

Notes:

- `conditions.search.Base` is empty – meaning "any dates, any locations, any operator".
- `conditions.search.Accommodation.Attributes` includes `location_ski_resorts`, one of many predefined attributes (see *Search Fields → Attributes* in the official docs).
- `filter` is empty – we rely only on `search` for now.
- `views.offerList` and `views.skiRegionList` are empty objects – defaults are used for both views.

---

## 4. Configuring search conditions

The `conditions` block lets you describe what you are looking for.

### 4.1. `conditions.search.Base`

This is the main place where you specify constraints such as:

- operators: `Base.Operator` (list of operator codes),
- dates: `Base.StartDate`, `Base.ReturnDate`, `Base.NightsBeforeReturn`,
- locations: `Base.DestinationLocation`, `Base.DepartureLocation`,
- components: `Base.ComponentsCombinations` (transport + accommodation types),
- price ranges: `Base.Price.FirstPerson.Min/Max`, `Base.Price.Total.Min/Max`,
- dataset / availability: `Base.DatasetInfo`, `Base.Availability`, etc.

The full list is in `components.schemas.baseconditions.Base` in the OpenAPI spec.

### 4.2. `conditions.search.Accommodation`

Key fields you may want to use:

- `Attributes` – array of strings; each is an attribute like:
	- `location_ski_resorts`, `location_mountains`, `location_near_the_slope`,
	- `facility_free_wifi`, `facility_parking`, `facility_for_families_with_childrens`,
	- `activity_winter_sports`, `activity_bicycles`, etc.
- `Category` – min/max category (e.g. 3–5).
- `Rating` – min/max rating (0–5 with halves).
- `XService` – standardized meal types.
- `Room`, `Rooms`, `Camp`, distance fields, etc.

### 4.3. `conditions.filter`

`filter` has the same structure as `search`, but is applied **after** the base search. Typical patterns:

- `search` – describes the broader, stable criteria (season, country, basic ski attribute).
- `filter` – used for interactive narrowing (price slider, rating, specific operators) based on the user’s current UI state.

---

## 5. Configuring views

The `views` object controls which data blocks the API returns.

The main ones for trip search are:

- `offerList`
- `skiRegionList`
- `regionList`
- `unfilteredFieldValues`
- `fieldValues`
- `groupedList`

### 5.1. `views.offerList`

`offerList` returns the main list of offers. Configuration options (from `components.schemas.views.offerList`):

- `limit` (int) – maximum number of results.
- `orderBy` (array of fields) – sort order; common fields include:
	- `Base.Price.Total`, `Base.Price.FirstPerson`,
	- `Accommodation.Name`, `Accommodation.Category`,
	- `Base.StartDate`, `Base.DepartureLocation`, etc.
- `orderByPriority` – advanced priority sorting (e.g. prefer certain durations or operators).
- `previousPageBookmark` (string) – opaque bookmark for pagination; pass the value from the previous response to fetch the next page.
- `multiroom` (bool) – enables multi-room response (one offer per board type).
- `fieldList` (array of strings) – restricts which fields are materialized in the offer; if omitted, a sensible default set is returned.

### 5.2. `views.skiRegionList` and `views.regionList`

These return offers grouped by:

- ski regions – `skiRegionList`,
- regular country/region hierarchy – `regionList`.

They share a similar shape:

- top-level is an object keyed by region ID,
- each value has:
	- `offer` – a representative offer object,
	- `path` (for ski regions) – an array describing the region hierarchy,
	- additional metadata.

Typical use cases:

- building a "browse by ski region" UI,
- letting the LLM summarize or compare regions rather than individual offers.

### 5.3. `views.unfilteredFieldValues` / `views.fieldValues`

These are especially useful for building dynamic filters:

- `unfilteredFieldValues` – possible values given only `search`.
- `fieldValues` – possible values given both `search` **and** `filter`.

They return arrays or maps of allowed values for selected fields, e.g. `Base.StartDate`, `Accommodation.Category`, `Accommodation.Attributes`, etc.

---

## 6. Understanding the response

The canonical `travelsearchresponse` schema has these top-level properties:

- `unfilteredFieldValues` (optional)
- `fieldValues` (optional)
- `regionList` (optional)
- `skiRegionList` (optional)
- `groupedList` (optional)
- `offerList` (optional)
- `debug` (string, optional)

### 6.1. `offerList`

Shape (`offerListResponse` in the spec):

- `sortKeyOrderAscending` – array of booleans (sort directions).
- `more` (bool) – `true` if there are more results to page through.
- `pageBookmark` (string) – bookmark to use in the next request for pagination.
- `items` – object whose keys are arbitrary IDs; each value is an `offerListResponseItem`:
	- `sortKeyValue` – sort keys for this offer.
	- `offer` – the actual `offer` object.
	- optionally `multiRoom` – multi-room variants.

The `offer` object itself has:

- `Base` – standardized offer fields (country/region/city, operator, dates, price, availability, etc.).
- `Accommodation` – accommodation-specific info (name, category, rating, attributes, coordinates, distances, services, etc.).
- `Transport` – transport details for flights/buses/trains.
- `Online` – available online actions (e.g. `checkstatus`).
- `AdditionalServices`, `Payment`, etc., depending on context.

### 6.2. `skiRegionList`

Shape (`skiRegionListResponse` in the spec):

- top-level object: keys are IDs (as strings),
- each value has:
	- `path` – array describing the ski-region hierarchy (country/region/etc.),
	- `offer` – an `offer` object (same structure as above).

---

## 8. Further reference

For complete field definitions and examples, consult the official Merlin Web Services documentation:

- Main docs: https://docu.mdsws.merlinx.pl/mdswsv5/public/static.html
- `/search` endpoint: https://docu.mdsws.merlinx.pl/mdswsv5/public/static.html#tag/Trips/operation/travelSearch

This note is intentionally high-level. When in doubt, prefer the official documentation at `./api.yml` and online documentation at `https://docu.mdsws.merlinx.pl/mdswsv5/public/static.html`.

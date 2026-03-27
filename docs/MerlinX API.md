# MerlinX API — przewodnik wdrożeniowy (portal wariantowy)

Ten dokument jest **drugim krokiem wdrożenia** po `wdrozenie.md` i ma jeden cel: nauczyć Cię **jak bezpiecznie i skutecznie pracować z API MerlinX** (głównie **MWS v5**, częściowo **MDSWS BookV4 v4** w zakresie używanym w tym repo).

Dokument bazuje na:

- dokumentacji w `docs/api/` (w szczególności `docs/api/api-v5.yml` i plikach MDSWS),
- przykładach odpowiedzi w `docs/api/responses/`,
- aktualnym kodzie integracji w `src/Service/MerlinX/*`, `src/Infrastructure/MerlinX/*` oraz `src/Infrastructure/Mdsws/*`.

> Uwaga o warstwach: zasady "MerlinX jako system obcy" i Anti‑Corruption Layer są opisane w `docs/architecture.md`. Tutaj skupiamy się na praktyce: jak wołać endpointy, jak czytać odpowiedzi i jak to się przekłada na portal.

---

## 1. Co w praktyce daje MerlinX w portalu

> W tym projekcie MerlinX jest **źródłem prawdy** o ofertach: listy, szczegóły, warianty, ceny, dostępność.

Portal integruje się z MerlinX w trzech "kanałach":

1. **MWS v5 (JSON/REST)** — wyszukiwanie, widoki (offerList/fieldValues/regionList), szczegóły oferty, online-check dostępności i ceny.
2. **MDSWS BookV4 (XML)** — operacje rezerwacyjne; w aktualnej wersji projektu realnie używamy głównie **BookV4 `check` jako generator formularza rezerwacyjnego** (i walidator dostępności).
3. **datacf (legacy, XML przez HTTP GET)** — dogrywanie treści/zdjęć hotelu; to nie jest "MWS v5", ale jest częścią ekosystemu MerlinX i jest używane w szczegółach oferty.

Dlaczego to jest rozdzielone?

- MWS v5 ma świetne "read" API (wyszukiwanie i prezentacja).
- BookV4 jest w praktyce nadal **jedyną stabilną ścieżką do rezerwacji** (check/optionbooking/optionconfirm/status), więc tego nie zastąpisz samym v5.
- datacf bywa potrzebne, bo teksty/galerie są niedostępne w samym endpointcie `/details`, które przedstawia tylko linki do tych zasobów, które trzeba potem osobno podlinkować.

---

## 2. Najważniejsze reguły pracy z MerlinX (dlaczego "tak", a nie inaczej)

### 2.1. Tylko backend rozmawia z MerlinX

- Tokeny i hasła **nie mogą** trafić do przeglądarki.
- Frontend może wysłać tylko parametry wyszukiwania/wyboru wariantu (nieufne), a backend decyduje co z nimi zrobić.

To w praktyce oznacza: jeśli potrzebujesz nowych danych z MerlinX dla UI, to:

1. dodajesz backendowy endpoint w routerze (`index.php`),
2. w kontrolerze walidujesz wejście,
3. wołasz usługę/use case,
4. renderujesz HTML/JSON.

### 2.2. Opis danych z frontendu, szczególnie `offerId`

> W UI często przenosimy `offerId` jako string. OfferId w API v4 było samym tylko id oferty wskazującym na konkretny obiekt i jakieś (nie udało mi się rozkodować reszty) innyparametry. W API v5 offerId jest **kompozytem** wskazującym nie tylko na obiekt i dodatkowe rzeczy jak w v4, ale też na konkretnego operatora oraz skład podróżujących ("pax" albo "paxMeta" = "participants"):

```
v4mainOfferId|4LetterOperatorCode|base64(paxMeta)
```

Rozkodowane paxMeta wygląta np. tak:

```
2|18.06.2020,15.01.2026|
```

I oznacza: 2 dorosłych i 2 dzieci/niemowląt urodzonych 18.06.2020 i 15.01.2026. Ostatnia część jest zawsze pusta, chociaż pewnie chcieli tam wrzucać osobno daty urodzenia niemowląt, bo MerlinX ogólnie wyróżnia właśnie: **dorosłych, dzieci, niemowlęta** i kiedy zwraca ceny to robi na podstawie tych kategorii. Kiedy później będziemy wysyłać zapytanie, możemy podać daty a MerlinX sam podzieli je na dorosłych, dzieci i niemowlęta, więc nie musisz ręcznie programować tego rozdzielenie.

W repo:

- parser do kompozytowego offerId: `src/Infrastructure/MerlinX/Auxiliary/OfferIdCompositeParser.php`
- dekoder samego paxMeta (best-effort, waliduje i ucina): `src/Infrastructure/MerlinX/Auxiliary/OfferIdPaxMetaDecoder.php`

Kluczowe:

- `paxMeta` traktujemy jako **nieufne** (może być zmienione w URL), więc oferta o danym offerId może nie pasować do danego paxMeta, jeżeli użytkownik zmienił je ręcznie w URL.
- do BookV4 `<ofr_id>` używamy **tylko `mainOfferId`** (czyli pierwszy segment).

### 2.3. API potrafi zwracać ogromne payloady → ograniczaj pola i cache’uj

MerlinX `/search` (i trochę `/searchbase`) potrafią zwrócić bardzo duże odpowiedzi.

W portalu stosujemy trzy strategie:

- **`fieldList`** w widokach (np. `views.fieldValues.fieldList`) — prosimy tylko o listę wartości, które występują pod wymienionymi w zapytaniu polami we wszystkich rezultatach.
- **`limit`** — domyślne `1000` w `SearchQuery`, a w "sondujących" zapytaniach czasem `1`. Domyślne 1000, bo czasem jeżeli nie ustawiło się limitu w ogóle, to MerlinX v5 zwracał jakąś randomową małą ilość jak 18 albo 23 wyniki i jednocześnie ustawiał `more=true` (co oznacza, że jest więcej wyników dla tego zapytania), co powodowało, że portal musiał robić wiele requestów, żeby zebrać pełny zestaw danych.
- **cache plikowy** w `var/cache/` — token, searchbase, fieldValues, szczegóły oferty, dostępność transportu itd. (patrz `config.php` → `merlinx.cache.*`).

### 2.4. Rozdzielamy "szukanie" od "sprawdzenia na żywo"

> Z dokumentacji MDSWS: wyniki listy ofert potrafią być zcache’owane po stronie MerlinX, więc mogą nie uwzględniać najnowszej dostępności/ceny. W praktyce:

- `/v5/data/travel/search` służy do **wyszukiwania** ofert (nie wiemy jeszcze czy na 100% dostępnych i w zwróconej cenie) i budowania UI.
- `/v5/data/travel/checkonline` to **autorytatywna walidacja online** z której ceny i dostępności dla konkretnej oferty/akcji są sprawdzane bezpośrednio u operatora w czasie rzeczywistym.
- BookV4 `check` to źródło danych do budowania formularza rezerwacyjnego. Może posłużyć w miejsce `checkonline` do potwierdzenia dostępności/ceny, ale dla wygody portal używa `checkonline`, bo token v5 jest już dostępny po stronie aplikacji.

### 2.4.1. Kanoniczny model dostępności w skionline.pl

W kodzie availability MerlinX jest modelowane **na dwóch niezależnych osiach**:

1. `baseStatus`:
   - `AVAILABLE`
   - `ON_REQUEST`
   - `NOTAVAILABLE`
   - `UNKNOWN`
2. `bookingForms`:
   - `firm`
   - `option`
   - `quota`

To rozdzielenie jest ważne, bo `firm/option/quota` **nie decydują o samej dostępności**. One tylko mówią, jakie kontrakty rezerwacyjne są możliwe, jeśli oferta w ogóle jest dostępna.

#### Jak liczymy `baseStatus`

Źródłem bazowego statusu jest wyłącznie `Base.Availability.base`:

- `available` -> `AVAILABLE`
- `onrequest` -> `ON_REQUEST`
- `notavailable` -> `NOTAVAILABLE`
- brak / `unknown` / śmieci -> `UNKNOWN`

`UNKNOWN` pozostaje stanem diagnostycznym, ale biznesowo jest traktowane tak samo jak `NOTAVAILABLE`.

#### Jak liczymy `bookingForms`

- dla `AVAILABLE` i `ON_REQUEST`:
  - `firm=true`
  - `option = Base.Availability.option.possible`
  - `quota = Base.Availability.quota.possible`
- dla `NOTAVAILABLE` i `UNKNOWN`:
  - `firm=false`
  - `option=false`
  - `quota=false`

To oznacza wprost:

- `ON_REQUEST` nie jest formą rezerwacji
- `ON_REQUEST` może równocześnie mieć `firm`, `option` i/lub `quota`
- `AVAILABLE` może mieć tylko `firm`, albo `firm+option`, albo `firm+quota`, albo `firm+option+quota`

#### Jak liczymy inquiryability i widoczność na listach

`isInquiryable` oraz widoczność oferty na listach zależą tylko od `baseStatus`:

- `AVAILABLE` -> `true`
- `ON_REQUEST` -> `true`, ale tylko jeśli przejdzie regułę minimum dni
- `NOTAVAILABLE` -> `false`
- `UNKNOWN` -> `false`

Reguła minimum dni (`merlinx.inquiryable_onrequest_min_days`) działa **wyłącznie** dla `ON_REQUEST`.

#### Gdzie który payload jest źródłem prawdy

- `search`, `search fragment`, `search json`:
  - używają `Base.Availability` z `/v5/data/travel/search`
  - to jest **coarse visibility** dla list
- `details`, `variant check`, promoted runtime, panel curated offers:
  - używają `/v5/data/travel/checkonline`
  - to jest **runtime truth** dla konkretnej oferty

W obu ścieżkach działa ten sam wspólny evaluator availability; różni się tylko payload wejściowy (`/search` vs `/checkonline`).

### 2.5. "Debug" w odpowiedziach jest wrażliwy → usuwamy go

W przykładach w `docs/api/responses/*` zobaczysz pole `debug`. Zawiera ono m.in. informacje o auth i requestach.

W aplikacji świadomie to usuwamy na poziomie transportu:

- `src/Service/MerlinX/MerlinXResponseWrapper.php` usuwa `debug` z JSON.
- `src/Service/MerlinX/MerlinXHttpClient.php` loguje request/response, ale redaguje tokeny w nagłówkach.

> Wniosek dla Ciebie: **nie zakładaj, że "debug" będzie dostępny w runtime oraz przypilnuj jego usunięcia przed logowaniem, a najlepiej natychmiast po otrzymaniu odpowiedzi, jeżeli nie planujesz świadomie i tylko na loklanej maszynie wykorzystać tego pola do debugowania**.

---

## 3. MWS v5 — autoryzacja i transport (token, nagłówki, retry)

### 3.1. Token `/v5/token/new`

Spec: `docs/api/api-v5.yml` → `components.schemas.auth` oraz `paths./v5/token/new`.

- Endpoint: `POST {base_url}/v5/token/new`
- Body: obiekt `auth` (wymagane m.in. `login`, `domain`, `source`, `type`; u nas wysyłamy też `password` i `expedient`)
- Odpowiedź: `{ "token": "..." }`

W projekcie:

- konfiguracja: `config.php` → `merlinx.base_url`, `merlinx.login`, `merlinx.password`, `merlinx.expedient`, `merlinx.domain`, `merlinx.source`, `merlinx.type`, `merlinx.language`
- pobieranie + cache: `src/Service/MerlinX/TokenProvider.php`
- cache plikowy: `var/cache/merlinx-token.json` (TTL z `config.php` → `merlinx.cache.token.ttl`)

> Ważne: w specyfikacji `type=web` ma opis "autoexpire, max ~20 min, lifetime niegwarantowany". I dobrze, tego chcemy, bo to jest bezpieczne rozwiązanie.

Z tego wynika, że:

- TTL w naszym cache to tylko **optymalizacja**,
- a realnym zabezpieczeniem jest mechanizm "odśwież token i ponów raz" po błędzie auth (patrz niżej).

### 3.2. Nagłówki dla requestów v5

Zgodnie ze spec (TokenAuth):

- `X-TOKEN: <token>`
- `X-DOMAIN: <domain>` (wartość z `config.php -> merlinx.domain`, niezależna od publicznej domeny portalu)

W projekcie dodaje je automatycznie `src/Service/MerlinX/MerlinXHttpClient.php` dla wszystkich requestów poza `/v5/token/new`, bo tam nie jest to potrzebne.

### 3.3. Retry i odświeżanie tokenu

`MerlinXHttpClient` robi dwie rzeczy, które musisz znać:

1. **Auth retry**: jeśli dostanie status `412` albo body wygląda jak `autherror`, to:
   - odświeża token (`TokenProvider::forceRefresh()`),
   - ponawia request **raz** z nowym tokenem.

2. **Retry na błędy 4xx/5xx**: przy niepowodzeniu robi do 3 prób z krótkim sleepem.

> Wniosek: jeśli dopisujesz nowe wołania v5, używaj `MerlinXHttpClient`, a nie "gołego" `HttpClient::create()`.

### 3.4. Minimalne requesty (do ręcznego testowania / zrozumienia API)

Poniższe przykłady są "czysto‑HTTP" (bez wrapperów z projektu), żebyś mógł szybko skojarzyć co jest czym. W aplikacji i tak wołamy to przez `MerlinXHttpClient` i serwisy w `src/Service/MerlinX/*`.

W konsoli stwórz takie zmienne środowiskowe:

```bash
BASE=https://mwsv5pro.merlinx.eu
DOMAIN=<merlinx-domain-z-config.php>
```

**1) Token**

```bash
curl -sS -X POST "$BASE/v5/token/new" \
  -H 'Content-Type: application/json' \
  -d '{
    "login": "LOGIN",
    "password": "PASSWORD",
    "expedient": "EXPEDIENT",
    "domain": "'"$DOMAIN"'",
    "source": "B2C",
    "type": "web",
    "language": "pl"
  }'
```

**2) Searchbase**

Specyfikacja formalnie pokazuje bardziej rozbudowany payload, ale w praktyce w tym projekcie wysyłamy `{}` żeby dostać po prostu całą paletę możliwych wartości, które potem zawężamy, bo zawężanie bezpośrednio w zapytaniu do searchBase jakoś nie działało (patrz `SearchBaseService`).

```bash
curl -sS -X POST "$BASE/v5/data/travel/searchbase" \
  -H "X-TOKEN: $TOKEN" -H "X-DOMAIN: $DOMAIN" \
  -H 'Content-Type: application/json' \
  -d '{}'
```

**3) Search (offerList + fieldValues)**

Minimalny przykład do listy ofert + budowy filtrów:

```bash
curl -sS -X POST "$BASE/v5/data/travel/search" \
  -H "X-TOKEN: $TOKEN" -H "X-DOMAIN: $DOMAIN" \
  -H 'Content-Type: application/json' \
  -d '{
    "conditions": {
      "search": {
        "Base": {},
        "Accommodation": { "Attributes": ["location_ski_resorts"] }
      },
      "filter": {}
    },
    "results": {},
    "views": {
      "offerList": {
        "limit": 20,
        "orderBy": ["Base.Price.FirstPerson"]
      },
      "fieldValues": {
        "fieldList": [
          "Base.StartDate",
          "Base.NightsBeforeReturn",
          "Transport.Type",
          "Accommodation.XService",
          "Accommodation.Room"
        ]
      }
    }
  }'
```

**4) Details**

```bash
OFFER_ID="COMPOSITE_OFFER_ID"
```

`offerId` możesz wziąć np. z parametru URL albo z `offer.Base.OfferId` w odpowiedzi z  `/search`.

```bash
curl -sS "$BASE/v5/data/travel/details?Base.OfferId=$OFFER_ID" \
  -H "X-TOKEN: $TOKEN" -H "X-DOMAIN: $DOMAIN" \
  -H 'Accept: application/json'
```

**5) Checkonline (status/cena)**

Najpierw pobierz `action` z `offer.Online.actions[*].action` (z `/search` lub `/details`).

```bash
curl -sS -X POST "$BASE/v5/data/travel/checkonline" \
  -H "X-TOKEN: $TOKEN" -H "X-DOMAIN: $DOMAIN" \
  -H 'Content-Type: application/json' \
  -d '{
    "offerIds": ["'"$OFFER_ID"'"],
    "actions": ["checkstatus"],
    "includeTFG": true
  }'
```

> Pomimo że w body API spodziewa się listy pod `offerIds`, to API MerlinX obsługuje tylko **1 offerId na request**, więc ta lista musi mieć dokładnie jeden element.

---

## 4. MWS v5 — endpointy używane w portalu (co robią i jak je stosować)

Poniżej najważniejsze endpointy z punktu widzenia tego projektu. Każdy z nich ma "partnera" w kodzie + przykładową odpowiedź w `docs/api/responses/`.

### 4.1. `/v5/data/travel/searchbase` — słownik pól i możliwych wartości

**Po co istnieje:** dostarcza "metadane wyszukiwania": listy operatorów, kombinacje komponentów, zakresy liczbowe, listę atrybutów itp.

- Spec: `docs/api/api-v5.yml` → `paths./v5/data/travel/searchbase`
- Przykład odpowiedzi: `docs/api/responses/searchBase.json`

W projekcie:

- fetch + cache: `src/Service/MerlinX/SearchBaseService.php`
- port/adapter: `src/Infrastructure/MerlinX/Adapter/MerlinXSearchBaseAdapter.php`

Dlaczego cache’ujemy:

- odpowiedź jest duża,
- dane zmieniają się rzadko,
- a UI potrzebuje tego często (np. budowa opcji transportu/filtrów).

Praktyczna wskazówka:

- `/searchbase` mówi, co jest "teoretycznie możliwe", ale nie co jest "aktualnie dostępne" (patrz `TransportAvailabilityService` w sekcji 6.2).

### 4.2. `/v5/data/travel/search` — serce aplikacji (wyszukiwanie + widoki)

**Po co istnieje:** jednym endpointem pobierasz jednocześnie:

- listę ofert (`offerList`),
- listy grup (`groupedList`, `regionList`),
- wartości do filtrów (`fieldValues`, `unfilteredFieldValues`).

Co w połączeniu z systemem cache daje szybkie działanie aplikacji.

Spec: `docs/api/api-v5.yml` → `paths./v5/data/travel/search`  
Szybki przewodnik (opiniowany): `docs/api/api.md`  
Przykłady odpowiedzi:

- `docs/api/responses/search_offerList.json` (offerList)
- `docs/api/responses/search_groupedList*.json` (groupedList)
- `docs/api/responses/search_regionList.json` (regionList)
- `docs/api/responses/search_single_offer_info.json` (mały, "techniczny" przykład)

#### Struktura requestu (w praktyce)

Request ma trzy główne części:

1. `conditions.search` — bazowe warunki (stałe, "co szukamy"),
2. `conditions.filter` — dodatkowe zawężenie (dynamiczne, np. "co user wyklikał"); ogólnie istnieje ono po to, żeby MerlinX mógł skorzystać ze swojego cache pod dane warunki z "search", a nie musiał robić całego wyszukiwania od zera, więc warto go używać żeby szybciej dostać odpowiedź z API.
3. `views` (+ opcjonalnie `results`) — _co_ ma zwrócić.

W kodzie nie kleimy tego ręcznie — używamy DTO (data transfer object):

- `src/Infrastructure/MerlinX/DTO/SearchQuery.php`

A request wykonujemy przez:

- `src/Service/MerlinX/SearchService.php`, który wykonuje canonical search-engine requests i scala wyniki.

Dlaczego DTO i `getAsObject()` są ważne w PHP:

- MerlinX rozróżnia `[]` i `{}`, bo pracuje na formacie JSON.
- W PHP "pusty obiekt" musisz wyrazić jako `new \stdClass()`, inaczej wyślesz tablicę.
- `SearchQuery::getAsObject()` i `ToObjectDeep` dbają o poprawność formatu danych (oraz o to, żeby niektóre pola były listami, a nie obiektami).
- pracując z DTO masz pewność, że nie pomylisz struktur, bo IDE podpowie Ci pola, a PHP będzie walidować typy w czasie wykonywania.
- w jednym miejscu możesz dodać domyślne wartości (np. `results`) i obsłużyć formatowanie, serializację, transformacje danych itd. dla całego projektu, niezależnie od miejsca w którym DTO używasz, a więc taki obiekt odpowiedzi będzie się zachowywał identycznie i posiadał taką samą strukturę i metody niezależnie od tego czy użyjesz go w serwisie, czy do sbudowania nowego zapytania, czy w kontrolerze, czy wrzucisz do logów, oraz nie musisz pamiętać o formatowaniu danych, bo stworzenie DTO samo o to zadba zgodznie z tym, co umieścisz w jego konstruktorze.

#### Warunki (`conditions`) w v5 — jak to czytać i jak MerlinX interpretuje request

W dokumentacji v5 (OpenAPI) część `conditions.search`/`conditions.filter` jest opisana jako `baseconditions`. To jest “pakiet” reguł filtrowania, podzielony logicznie na bloki:

- `Base` — pola wspólne (daty, operator, destynacja, dostępność, cena, kombinacje komponentów, uczestnicy itd.).
- `Accommodation` — pola dotyczące zakwaterowania (typ, standard, atrybuty, wyżywienie, pokój, dystanse…).
- `Transport` — **parametry transportu**, ale uwaga: nie wybór “czy oferta ma bus/flight/train”, tylko np. filtry lotu (bagaż, przesiadki…).
- czasem dodatkowe bloki (`Content`, `Custom`), zależnie od instalacji/produktu.

Najważniejsza zasada: **MerlinX nie ma “jednego uniwersalnego filtra” transportu w stylu `Base.transport=bus`**. Obecność transportu i typ pakietu filtruje się przez `Base.ComponentsCombinations` (patrz niżej).

Druga zasada: w wielu miejscach v5 używa list nawet wtedy, gdy wybierasz 1 element. Przykłady z `baseconditions.Base`:

- `Base.Operator` to lista (nawet dla jednego operatora),
- `Base.Availability` to lista statusów,
- `Base.Catalog`, `Base.Transfer`, `Base.Refundable`, `Base.Resident` to listy,
- `Base.ComponentsCombinations` to lista kombinacji, gdzie każda kombinacja to lista komponentów (`array<array<string>>`).

Trzecia zasada: typy zakresów nie są spójne między polami:

- daty: `Base.StartDate` używa `Min`/`Max` (a `After`/`Before` są zdeprecjonowane w specyfikacji),
- noce: `Base.NightsBeforeReturn` używa `min`/`max` (małe litery),
- ceny: pola cenowe mają `.Min`/`.Max` (np. `Base.Price.FirstPerson.Min`).

Wniosek: zawsze weryfikuj kształt pola w `docs/api/api-v5.yml` zamiast zgadywać.

##### Przykłady najczęstszych filtrów w `conditions.*` (v5)

Poniżej kilka “klocków” requestu, które w praktyce składasz razem. Zakładamy, że mówimy o `/v5/data/travel/search` i strukturze:

```json
{
  "conditions": { "search": { /* ... */ }, "filter": { /* ... */ } },
  "results": {},
  "views": { /* ... */ }
}
```

**1) Daty wyjazdu (`Base.StartDate`)**

- dowolne od konkretnej daty:

```json
{ "Base": { "StartDate": { "Min": "2026-01-01" } } }
```

- dokładnie jeden dzień (min = max):

```json
{ "Base": { "StartDate": { "Min": "2026-01-15", "Max": "2026-01-15" } } }
```

**2) Długość pobytu (`Base.NightsBeforeReturn`)**

To jest liczba nocy (dla ofert z transportem: różnica między start/return; dla samych noclegów: liczba nocy zakwaterowania).

```json
{ "Base": { "NightsBeforeReturn": { "min": 7, "max": 7 } } }
```

**3) Operatorzy (`Base.Operator`)**

```json
{ "Base": { "Operator": ["VITX", "SNOW"] } }
```

**4) Dostępność (`Base.Availability`)**

```json
{ "Base": { "Availability": ["available", "onrequest"] } }
```

**5) Uczestnicy (`Base.ParticipantsList`)**

W v5 lista uczestników jest częścią warunków i może wpływać na cenę/dostępność. Każdy element listy jest jednym z:

- `{ "code": "ADULT" | "CHILD" | "INFANT" | ... }`
- `{ "birthdate": "YYYY-MM-DD" }`
- `{ "age": 7 }`

Przykład: 2 dorosłych + 1 dziecko (wiek z daty urodzenia):

```json
{
  "Base": {
    "ParticipantsList": [
      { "code": "ADULT" },
      { "code": "ADULT" },
      { "birthdate": "2018-05-12" }
    ]
  }
}
```

W schemacie jest też `roomIndex` (przydatne przy multi-room), ale jeśli nie obsługujesz multi-room, zwykle możesz go pominąć.

#### Transport w v5: `Base.ComponentsCombinations` (a nie `Transport.Type` i nie `Transport.*` w Base)

Jeżeli chcesz wyszukać oferty z konkretnym środkiem transportu, to robisz to przez:

- `conditions.*.Base.ComponentsCombinations`

To pole mówi MerlinX “jakie komponenty ma zawierać oferta”. Przykładowe komponenty:

- `transport.flight`, `transport.bus`, `transport.train` (transport),
- `transport.*` (wildcard: dowolny transport),
- `accommodation.*` (wildcard: dowolne zakwaterowanie),
- `accommodation.hotel`, `accommodation.apartament`, `accommodation.trip` itd. (konkretne typy zakwaterowania).

Przykłady typowych kombinacji (jedna “gałąź” pobytu):

```json
{
  "Base": {
    "ComponentsCombinations": [["accommodation.*", "transport.*"]]
  }
}
```

```json
{
  "Base": {
    "ComponentsCombinations": [["accommodation.*", "transport.flight"]]
  }
}
```

```json
{
  "Base": {
    "ComponentsCombinations": [["accommodation.*", "transport.bus"]]
  }
}
```

```json
{
  "Base": {
    "ComponentsCombinations": [["accommodation.*"]]
  }
}
```

Oferty “złożone” (np. objazdówka, 2in1 hotel+hotel, Z+W) mogą wymagać **wielu gałęzi** w `ComponentsCombinations`. Wtedy wysyłasz kilka list komponentów (każda lista to osobny segment pobytu), np.:

```json
{
  "Base": {
    "ComponentsCombinations": [
      ["transport.flight", "accommodation.trip"],
      ["accommodation.hotel"]
    ]
  }
}
```

To jest opisane w `docs/api/api-v5.yml` w sekcji “ComponentCombinations”. Zawsze sprawdzaj też `/searchbase`, bo to on pokazuje, które kombinacje są realnie wspierane w danym środowisku.

Skąd wiedzieć jakie komponenty są legalne?

- z `/v5/data/travel/searchbase` (`Base.ComponentsCombinations.componentscombinations`),
- z dokumentacji “Search Fields → ComponentCombinations” w `docs/api/api-v5.yml`.

**Uwaga 1:** `Transport.Type` występuje w `fieldValues`, ale to jest **wyjście/etykieta do UI**, a nie filtr wejściowy.  
**Uwaga 2:** `conditions.Transport` służy do zawężania parametrów transportu *w obrębie już wybranego komponentu*, np. dla lotów:

```json
{
  "Transport": {
    "Flight": {
      "Stops": ["no"],
      "Luggage": ["yes"]
    }
  }
}
```

Żeby to miało sens, oferta i tak musi spełniać `ComponentsCombinations` z `transport.flight`.

#### Destynacja (kraj/region) w v5: `Base.DestinationLocation` i format ID (`15:` vs `15_13`)

W v5 filtrowanie po destynacji jest realizowane przez:

- `Base.DestinationLocation` (typ `conditions.location` albo geolokalizacja).

Wariant “po ID z hierarchii kraj/region” wygląda tak:

```json
{
  "Base": {
    "DestinationLocation": {
      "Id": ["15_13"]
    }
  }
}
```

Kluczowe: `Id` to **lista stringów**, a same identyfikatory mają konkretne formaty, które zobaczysz m.in. w widoku `regionList`:

- **kraj**: `"15:"` (z dwukropkiem na końcu),
- **region**: `"15_13"` (kraj + podkreślenie + region).

W przykładzie `docs/api/responses/search_regionList.json`:

- `regionList["15:"].desc` to `"Hiszpania"`,
- `regionList["15:"].regions` zawiera m.in. klucz `"15_13"` (np. `"Andaluzja"`).

W samych ofertach (np. `docs/api/responses/single_offer_example0.json`) zobaczysz zwykle:

- `offer.Base.DestinationLocation.Id = "15_13"` oraz nazwę `"Hiszpania / Andaluzja"`.

Jak pozyskać te ID z samego API (bez “zgadywania”):

1) wywołaj `/v5/data/travel/search` z widokiem `regionList` (może być nawet bez warunków):

```json
{
  "conditions": { "search": { "Base": {} }, "filter": {} },
  "results": {},
  "views": { "regionList": {} }
}
```

2) odczytaj klucze w `regionList` i `regionList[*].regions` (to są ID, których używasz w `DestinationLocation.Id`).

Jak użyć tego praktycznie (przykłady):

- **wszystkie oferty do kraju**: `DestinationLocation.Id = ["15:"]`
- **oferty do konkretnego regionu**: `DestinationLocation.Id = ["15_13"]`
- **multi-select destynacji**: `DestinationLocation.Id = ["15_13", "44_1220"]`

Jeśli zamiast hierarchii chcesz szukać “po mapie”, `DestinationLocation` może też przyjąć geolokalizację (circle/polygon) — patrz `conditions.geolocation` w `docs/api/api-v5.yml`.

Analogicznie działa `Base.DepartureLocation` (miejsce wyjazdu): to też `conditions.location` z listą `Id`. W praktyce identyfikatory miejsc wyjazdu często wyglądają jak `0x...` (hex‑prefiks) — traktuj je jako opaque i nie próbuj ich parsować; bierz je z `fieldValues`/`searchbase` i przekazuj dalej jako string.

#### `Accommodation.Attributes` w v5 — składnia `+` / `-` i logika OR

Pole `Accommodation.Attributes` przyjmuje listę stringów, ale ma dodatkową składnię:

- domyślnie: “oferta ma mieć co najmniej jeden atrybut z listy” (logika OR),
- `+prefiks`: atrybut **wymagany**,
- `-prefiks`: atrybut **wykluczony**.

Ta zasada jest opisana wprost w `docs/api/api-v5.yml` przy `baseconditions.Accommodation.Attributes`.

Przykład:

```json
{
  "Accommodation": {
    "Attributes": [
      "+facility_free_wifi",
      "-facility_for_adult",
      "facility_indoor_pool",
      "facility_outdoor_pool"
    ]
  }
}
```

Interpretacja:

- musi mieć `facility_free_wifi`,
- nie może mieć `facility_for_adult`,
- i musi mieć co najmniej jeden z: `facility_indoor_pool` lub `facility_outdoor_pool`.

#### `results.groupBy` + `groupedList` — jak działa grupowanie wyników w `/search`

Mechanizm grupowania w v5 składa się z dwóch elementów requestu:

1) `results.groupBy` — mówi *po czym grupować* (i opcjonalnie *dla jakiej grupy zawęzić ofertę*),  
2) `views.groupedList` — mówi *że chcesz dostać wynik w formie listy grup*.

To jest często wykorzystywane do UX typu “lista obiektów (hoteli) → po kliknięciu pokaż terminy/konfiguracje”.

##### 1) `results.groupBy` — klucz i (opcjonalnie) wartość

Fragment requestu (wg `docs/api/api-v5.yml`):

```json
{
  "results": {
    "groupBy": {
      "key": "Accommodation.XCode",
      "value": "203403"
    }
  }
}
```

- `key` — nazwa pola, po którym MerlinX ma grupować wyniki (z punktu widzenia modelu oferty).
- `value` — **opcjonalny** wybór konkretnej grupy; jeśli ustawisz `value`, to `offerList` może zostać zawężony do tej jednej grupy (szczególnie przydatne w kroku “drill‑down”).

Dozwolone wartości `key` są zdefiniowane w specyfikacji przy `/v5/data/travel/search` (enum w `results.groupBy.key`), np.:

- `Accommodation.XCode` (najczęstsze: obiekt/hotel),
- `Base.StartDate` (grupowanie po dacie startu),
- `Base.DepartureLocation` (grupowanie po miejscu wyjazdu),
- `Base.DestinationLocation` (grupowanie po destynacji),
- `Accommodation.Room`, `Accommodation.Service`, `Accommodation.Category` itd.

> Uwaga praktyczna: w przykładach w `docs/api/responses/search_groupedList*.json` pole `debug.requests[*].GroupBy` potrafi mieć wartość `Accomodation.XCode` (literówka). Trzymaj się specyfikacji (`Accommodation.XCode`), ale miej świadomość, że w realnych środowiskach MerlinX możesz spotkać historyczne nazwy.

##### 2) `views.groupedList` — jak wygląda odpowiedź i co realnie dostajesz

Żeby dostać `groupedList` w odpowiedzi, musisz:

- ustawić `results.groupBy.key`,
- poprosić o widok `views.groupedList`.

Minimalny request (grupowanie po obiekcie/hotelu):

```json
{
  "conditions": {
    "search": { "Base": {} },
    "filter": {}
  },
  "results": {
    "groupBy": { "key": "Accommodation.XCode" }
  },
  "views": {
    "groupedList": {
      "limit": 50,
      "orderBy": ["Base.Price.FirstPerson"]
    }
  }
}
```

W odpowiedzi dostajesz:

```json
{
  "groupedList": {
    "items": {
      "203403": {
        "groupKeyValue": "203403",
        "sortKeyValue": [13],
        "offer": { /* oferta-reprezentant */ }
      }
    },
    "more": true,
    "pageBookmark": "..."
  }
}
```

Jak to interpretować:

- `items` to **mapa**: kluczami są wartości grup (np. `XCode`), a wartościami są obiekty `groupedListResponseItem`.
- `groupKeyValue` to wartość grupy jako string (często powtórzona względem klucza mapy).
- `offer` to **jedna oferta** przypisana do grupy (oferta‑reprezentant). To nie jest “lista wszystkich ofert w grupie”.
- `sortKeyValue` i `sortKeyOrderAscending` opisują sortowanie (zależne od `views.groupedList.orderBy`).
- `more`/`pageBookmark` działają jak paginacja: jeśli `more=true`, to w kolejnym wywołaniu ustawiasz:

```json
{
  "views": {
    "groupedList": { "previousPageBookmark": "..." }
  }
}
```

##### 3) Drill‑down: jak pobrać oferty dla wybranej grupy

Typowy flow “lista obiektów → szczegóły obiektu”:

1) Wywołaj `/search` z `groupBy.key=Accommodation.XCode` i `views.groupedList` → dostajesz listę obiektów.
2) Użytkownik wybiera `xCode` (czyli `groupKeyValue`).
3) Wywołaj `/search` ponownie z tym samym `groupBy.key`, ale dodaj `groupBy.value=<xCode>` i poproś o `views.offerList`:

```json
{
  "conditions": {
    "search": { "Base": {} },
    "filter": {}
  },
  "results": {
    "groupBy": {
      "key": "Accommodation.XCode",
      "value": "203403"
    }
  },
  "views": {
    "offerList": { "limit": 100, "orderBy": ["Base.StartDate"] }
  }
}
```

Efekt: `offerList` zawiera teraz tylko oferty należące do wybranego obiektu/hotelu (warianty terminów/pokoi/wyżywień).

##### 4) Sterowanie kolejnością grup: `prioritizeGroupValues` i `excludeGroupValues`

W `views.groupedList` są dodatkowe pola:

- `prioritizeGroupValues` — “podbij” wybrane grupy na początek (wg spec wspierane głównie dla `Accommodation.XCode`),
- `excludeGroupValues` — wyklucz konkretne grupy (pole zdeprecjonowane w spec).

Przykład:

```json
{
  "views": {
    "groupedList": {
      "prioritizeGroupValues": ["203403", "207106"],
      "limit": 50
    }
  }
}
```

#### Widoki (`views`) — wybieraj świadomie

To `views` decyduje o kosztach i czasie odpowiedzi.

W praktyce `views` to lista “zestawów wyników”, o które prosisz w jednym wywołaniu `/search`. Każdy klucz w `views` (np. `offerList`, `fieldValues`) powoduje, że w odpowiedzi pojawi się blok o tej samej nazwie. Jeśli nie poprosisz o widok — nie dostaniesz go w odpowiedzi.

Najważniejsze widoki (w kontekście v5 jako API):

- `offerList` — lista ofert spełniających warunki (z paginacją). W odpowiedzi dostajesz m.in.:
  - `items` — mapa wyników (klucze nie są częścią modelu domenowego; prawdziwy identyfikator oferty jest w `offer.Base.OfferId`),
  - `more` + `pageBookmark` — paginacja,
  - `sortKeyOrderAscending` — kierunki sortowania.
- `fieldValues` — “redukcja filtrów”: możliwe wartości pól *po zastosowaniu* `conditions.search` **i** `conditions.filter`.
- `unfilteredFieldValues` — “filtry bazowe”: możliwe wartości pól tylko na podstawie `conditions.search` (bez `filter`).
- `groupedList` — lista pogrupowana po polu `results.groupBy.key` (np. po `Accommodation.XCode`); przydatne do “listy hoteli”, gdzie każdy hotel ma ofertę‑reprezentanta.
- `regionList` — hierarchia kraj/region z ofertą‑reprezentantem w każdej gałęzi. Klucze są ważne (np. `"15:"`, `"15_13"`) i mogą być używane jako `DestinationLocation.Id`.
`fieldList` (w widokach) to mechanizm optymalizacji: prosisz tylko o konkretne pola / wartości, zamiast materializować ogromną ofertę. W praktyce:

- w `offerList.fieldList` ograniczasz pola w samych ofertach,
- w `fieldValues.fieldList` prosisz o listę pól, dla których MerlinX ma zwrócić “możliwe wartości” (np. `Base.StartDate`, `Base.DepartureLocation`, `Accommodation.Room`).

`fieldValues` i `unfilteredFieldValues` mają mieszaną strukturę danych (zależnie od pola):

- pola typu “lista dat” zwracają `array<string>` (np. `Base.StartDate`),
- pola typu “lista liczb” zwracają `array<int>` (np. `Base.NightsBeforeReturn`),
- pola typu “słownik ID → etykieta” zwracają `object` (np. `Base.DepartureLocation`, `Accommodation.Room`, `Transport.Type`), gdzie kluczem jest ID, a wartością opis/label.

Typowe wzorce w portalu (czyli gotowe use case’y na bazie powyższych prymitywów):

- UI listy ofert: `offerList` + ewentualnie `fieldValues` (do filtrów).
- UI "wariantów" oferty: `fieldValues` (daty/długości/wyżywienie/pokoje/transport), czasem `offerList` tylko po wybrane pola.
- UI destynacji: `regionList`.
- są jeszcze inne widoki - sprawdź dokumentację.

Paginacja:

- MerlinX potrafi zwracać `more=true` + `pageBookmark`.
- Następna strona to `views.<view>.previousPageBookmark = <bookmark>`.
- `SearchService` robi to automatycznie.

> `SearchService` pobiera kolejne strony i scala wyniki, zwracając DTO z odpowiedzią jakby przyszła tylko jedna duża odpowiedź z serwera.

### 4.3. `/v5/data/travel/details` — szczegóły jednej oferty

Spec: `docs/api/api-v5.yml` → `paths./v5/data/travel/details`  
Przykład: `docs/api/responses/details.json`

- Metoda: `GET`
- Query: `Base.OfferId=<offerId>`

W projekcie:

- repozytorium + cache: `src/Service/MerlinX/OfferDetailsService.php`
- adapter "strony szczegółów": `src/Infrastructure/MerlinX/Adapter/MerlinXOfferDetailsPageAdapter.php`
- mapowanie do VM: `src/Infrastructure/MerlinX/Acl/OfferDetailsPageVmMapper.php`
- dogrywanie treści/galerii (datacf): `src/Service/MerlinX/DataCf/OfferDetailsContentService.php`

Dlaczego cache’ujemy `/details` aż 7 dni (`config.php` → `merlinx.cache.details.ttl`):

- szczegóły (opisy, lokalizacja, atrybuty, zdjęcia-thumb) zmieniają się rzadko,
- "zmienne" rzeczy (cena/dostępność) i tak weryfikujemy przez `/checkonline` lub BookV4 za każdym razem kiedy użytkownik wybierze jakiś wariant oferty oraz przy wysyłaniu zapytania (a później i przy rezerwacji),
- dzięki temu strona działa szybciej i taniej.

### 4.4. `/v5/data/travel/checkonline` — online check statusu/ceny (autorytatywne)

Spec: `docs/api/api-v5.yml` → `paths./v5/data/travel/checkonline`  
Przykład: `docs/api/responses/checkonline.json`

- Metoda: `POST`
- Body:
  - `offerIds: [ "<offerId>" ]` (aktualnie 1 na request)
  - `actions: [ "<action>" ]` (aktualnie 1 na request)
  - `includeTFG: bool` (u nas zazwyczaj true)

Najważniejsza zasada: `action` nie jest "wymyślone" przez nas — bierze się je z oferty:

- `offer.Online.actions[*].action`

W praktyce w danych zobaczysz zwykle:

- `checkstatus` — sprawdza cenę i dostępność,
- `checktransport` — dociąga dane transportu (bus/train/flight) i np. licznik dostępnych pokoi.

W projekcie:

- transport: `src/Service/MerlinX/TravelCheckOnlineService.php`
- interpretacja do modelu domenowego: `src/Service/MerlinX/CheckOnlineAvailability.php`
- użycia: m.in. `src/Service/OfferOnlineCheckService.php` oraz "warianty" (UI wyboru konfiguracji).

Dlaczego `/checkonline` jest tak ważne:

- oferty z `/search` mogą mieć ceny "niebookowalne" albo przestarzałe,
- `/checkonline` pokazuje realną dostępność i często zwraca komunikat operatora.

---

## 5. Jak czytać ofertę (v5) — mini‑słownik pól, które spotkasz codziennie

Na bazie przykładów z `docs/api/responses/*.json`:

### 5.1. `offer.Base`

Najczęściej używane:

- `OfferId` — identyfikator oferty (często kompozyt), przekazywany między stronami.
- `UniqueObjectId` — identyfikator obiektu (np. hotelu) niezależnie od terminu (często użyteczny do grupowania).
- `Operator` / `OperatorDesc` — operator (touroperator) i nazwa.
- `StartDate`, `ReturnDate`, `NightsBeforeReturn`, `Duration` — czas.
- `ComponentsCombinations` — skład oferty (np. `["transport.flight","accommodation.hotel"]`).
- `Availability` — status bazowy + dodatkowe flagi `option/quota`.
- `Price` / `OriginalPrice` — ceny; zwróć uwagę na `details.TFGIncluded`.
- `DepartureLocation`, `DestinationLocation` — lokalizacje (ID bywają w formie hex `0x...` → traktuj jako opaque).
- `Omnibus.URL` — link do "omnibus", czyli historii cen (zewnętrzny). Za jego pomocą można spełnić wymóg Unii o pokazywaniu najniższej ceny przed obniżką, gdybyśmy pokazywali, że gdzieś jest specjalna promocja/obniżka (ale tego nie robimy).

### 5.2. `offer.Accommodation`

- `XCode.Id` — kluczowy identyfikator obiektu do "wariantów".
- `Name`, `Category`, `Rating`, `Attributes` — opis i filtry.
- `Room`, `XService` — pokój i wyżywienie (w UI są to kluczowe wybory).
- dystanse: `DistanceToSlope`, `DistanceToCityCenter` itd.
- `ThumbUrl` — miniatura.

### 5.3. `offer.Transport`

Transport ma różne kształty zależnie od komponentu:

- flight: `Transport.Flight.Out/Ret[]` (z czasami i kodami lotnisk)
- bus: `Transport.Bus.Out/Ret` (z datami i kodami)
- train: `Transport.Train.Out/Ret`

Przykłady:

- `docs/api/responses/single_offer_flight_transport.json`
- `docs/api/responses/single_offer_bus_transport.json`
- `docs/api/responses/single_offer_train_transport.json`

### 5.4. `offer.Online.actions`

> To jest "lista dozwolonych online checków" dla danej oferty. Te checki online to są wspomniane wcześniej odpytania na żywo do samego operatora, które zawsze zwrócą aktualne dane.

---

## 6. Jak budujemy wyszukiwanie i filtry w `main-non-ski`

Tu zaczyna się kontekst projektu: jak używamy MWS v5 do budowy UI.

### 6.1. Canonical search engine (konfiguracja + merge)

W `main-non-ski` publiczne wyszukiwanie korzysta z `config.php -> merlinx.search_engine` i nie używa już `winter_*` ani `LegacySearchQuery`.

`SearchService`:

- buduje canonical search-engine requests,
- wykonuje je po kolei,
- scala odpowiedzi i, jak wspomniane wyżej, zwraca zbudowany z nich DTO jako jedną dużą odpowiedź.

To daje jedną kanoniczną definicję publicznego wyszukiwania bez utrzymywania legacy search variants.

### 6.2. "Czy transport w ogóle jest dostępny?" (dlaczego nie ufamy tylko `/searchbase`)

Problem: `/searchbase` mówi, że np. istnieje komponent `transport.train`, ale to nie znaczy, że _dziś_ są oferty z pociągiem.

Dlatego wspólna lista opcji transportu nie opiera się już na samym `/searchbase`. Używamy `src/Service/MerlinX/TransportAvailabilityService.php`, który:

- robi jedno zwykłe wyszukiwanie z `views.fieldValues.fieldList = ["Transport.Type"]`,
- mapuje surowe kody MerlinX (`H`, `BU`, `F`, `NF`, `T`, itd.) na 4 wewnętrzne rodziny transportu (`own`, `bus`, `flight`, `train`),
- cache’uje wynik na krótko,
- a `/searchbase` zostaje tylko fallbackiem, gdy lookup `fieldValues` zawiedzie.

> Wzorzec, który warto zapamiętać: **searchbase → "co środowisko wspiera", search fieldValues → "co pokazywać użytkownikowi jako realnie dostępne rodziny transportu"**.

### 6.3. `fieldValues` jako silnik wariantów i filtrów

`fieldValues` jest najtańszym sposobem, żeby z MerlinX wydobyć:

- dostępne daty startu,
- dostępne długości pobytu,
- listę transportów,
- listę wyżywień (`Accommodation.XService`),
- listę pokoi (`Accommodation.Room`),
- miejsca wyjazdu (`Base.DepartureLocation`).

W repo:

- budowanie opcji wariantów: `src/Service/OfferVariantsOptionsService.php`
- walidacja wybranej konfiguracji: `src/Service/OfferVariantCheckService.php`

> Kluczowy trik: czasem `fieldValues` nie wystarczy (np. godziny odjazdu/lotów) — wtedy dociągamy mały `offerList` z minimalnym `fieldList`.

### 6.4. Specyficzne zasady uczestników stosowane danego operatora

W MerlinX ceny i dostępność zależą od `Base.ParticipantsList`.

W projekcie mamy realny case: Snowtrex nie zwraca większości albo w ogóle żadnych wyników, jeśli dzieci są wymienione w zapytaniu, dlatego wysyłamy je jako `ADULT` (w `config.php` zapisane jako `child_as_adult`).

Dlatego:

- budujemy listę uczestników wprowadzoną przez użytkownika: `src/Service/ParticipantsListBuilder.php`
- budujemy grupy uczestników właściwe dla danego operatora: `src/Infrastructure/MerlinX/Acl/VariantOperatorSearchGroups.php`

Wniosek praktyczny: jeśli zmieniasz logikę uczestników, to wpływasz na ceny, dostępność i to, co MerlinX zwróci w `fieldValues`.

### 6.5. "On request" i minimalne wyprzedzenie

W MerlinX `Base.Availability.base` chcemy dostać tylko wyniki ofert o dostępności definiowanej jako `available`, `onrequest`, `unknown` (a więc oferty `notavailable` nas nie interesują - nie pokazujemy w wynikach wyszukiwania historycznych ofert).

W projekcie mamy politykę decydującą od jakiego minimalnego wyprzedzenia (czyli ile minimalne dni wcześniej) pokazujemy ofertę dostępną `onrequest`. Aktualnie jest to 21 dni przed planowanym startem wycieczki (ustawione w `config.php` → `merlinx.inquiryable_availability_policy.min_days_before_start_for_onrequest`).

Kod:

- `src/Infrastructure/MerlinX/Availability/InquiryableAvailabilityPolicy.php`
- splitter zapytań (oddziel `onrequest` i dostosuj daty zapytania tak, aby spełniały ustalony wymóg min. 21 dni): `src/Infrastructure/MerlinX/Auxiliary/InquiryableAvailabilitySearchSplitter.php`

> To jest kolejny ważny wzorzec: **czasem jedno "proste" wyszukiwanie trzeba rozbić na parę**, np. jak wyżej albo jak przy zapytaniach zwierających dzieci (osobne do operatora Snowtrex po przekształceniu dzieci w dorosłych).

---

## 7. Typowe pułapki i "debugowanie" integracji

1. **"Na liście jest cena, a potem się zmienia"**  
   To normalne. `/search` bywa cache’owany. Zawsze traktuj `/checkonline` i BookV4 `check` jako prawdę.

2. **"Token działał, nagle 412 / autherror"**  
   Token web może wygasnąć wcześniej niż TTL. `MerlinXHttpClient` powinien odświeżyć i ponowić request raz.

3. **"Nie widzę `debug` w odpowiedzi"**  
   Bo usuwamy. Jeśli potrzebujesz debugowania, rób to świadomie i nie loguj sekretów.

4. **"Te same parametry → inne wyniki dla innego operatora"**  
   Normalne: operatorzy różnie liczą uczestników, mają inne pokoje, inne słowniki. Stąd grupowanie operatorów i osobne listy uczestników.

5. **"StartDate/DepartureLocation niby są, ale UI pokazuje pusto"**  
   Sprawdź, czy prosisz o dobre pola (sprawdź zawartość `fieldValues.fieldList` w zapytaniu) i czy budujesz poprawne `ComponentsCombinations`.

6. **"BookV4 zwraca ERROR / PAX"**  
   To zwykle nie pasują uczestnicy do oferty. Czasem nie wiadomo dlaczego, bo posługujemy się normalnym offerId zwróconym przez MerlinX i takim samym składem uczestników jak przy zapytaniu w którym to offerId uzyskaliśmy. W naszym adapterze mapujemy to na czytelny błąd (`MdswsBookingFormProvider`).

---

## 8. Jak dopisywać nową integrację z MerlinX w tym repo (checklista dla juniora)

1. **Zacznij od dokumentacji i przykładów**
   - spec v5: `docs/api/api-v5.yml`
   - przykłady JSON/XML: `docs/api/responses/`

2. **Zdecyduj: v5 czy BookV4**
   - "read i wyszukiwanie" → v5
   - "rezerwacja i formularz" → BookV4

3. **Dodaj transport w odpowiedniej warstwie**
   - v5: nowy serwis w `src/Service/MerlinX/*` na bazie `MerlinXHttpClient`
   - BookV4: nowa metoda/flow w ramach `BookV4Port` + adapter `MdswsBookV4Client`

4. **Nie wypuszczaj kształtu MerlinX poza Infrastructure**
   - mapuj odpowiedzi do stabilnych struktur/ReadModeli
   - do UI dawaj tylko to, co potrzebne (najlepiej gotowe VM)

5. **Zadbaj o payload i cache**
   - `fieldList`, `limit`, paginacja (`pageBookmark`)
   - cache w `var/cache/` tam, gdzie ma sens (ale nie cache’uj online-checków!)

6. **Zadbaj o bezpieczeństwo**
   - walidacja wejścia z requestu
   - brak tokenów w logach / brak "debug" w UI

---

## 9. Przykładowe pytania/zadania (5–7) do samodzielnego przećwiczenia w kodzie

1. **Skąd bierzemy listę krajów i regionów do filtrów i jak ją cache’ujemy?**  
   Zobacz: `src/Service/MerlinX/SearchDestinationsService.php` (używa `views.regionList` z `/v5/data/travel/search`).

2. **Gdzie i jak obsługujemy paginację w `/v5/data/travel/search` (pageBookmark)?**  
   Zobacz: `src/Service/MerlinX/SearchService.php` (extract bookmarks → previousPageBookmark → merge).

3. **Jak wyznaczamy dostępne opcje wariantu (daty, długości, wyżywienie, pokoje, transport) dla hotelu `XCode`?**  
   Zobacz: `src/Service/OfferVariantsOptionsService.php` (widok `fieldValues` + minimalny `offerList`).

4. **Jak weryfikujemy, czy wybrana konfiguracja jest dostępna i jak proponujemy alternatywne terminy?**  
   Zobacz: `src/Service/OfferVariantCheckService.php` (fieldValues vs offerList, "closest past/future").

5. **Skąd bierzemy akcję do `/v5/data/travel/checkonline` i jak interpretujemy wynik?**  
   Zobacz: `src/Service/MerlinX/TravelCheckOnlineService.php` oraz `src/Service/MerlinX/CheckOnlineAvailability.php`.

6. **Jak działa polityka "on request" i dlaczego rozbijamy wyszukiwanie na dwa warianty?**  
   Zobacz: `src/Infrastructure/MerlinX/Availability/InquiryableAvailabilityPolicy.php` oraz `src/Infrastructure/MerlinX/Auxiliary/InquiryableAvailabilitySearchSplitter.php`.

7. **Jak powstaje formularz rezerwacji z BookV4 `check` i jak mapujemy go do pól UI?**  
   Zobacz: `src/Infrastructure/Mdsws/BookingForm/MdswsBookingFormProvider.php` + `src/Infrastructure/Mdsws/BookingForm/Auxiliary/BookingFormMapper.php`.

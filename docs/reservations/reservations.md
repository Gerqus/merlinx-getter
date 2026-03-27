# Rezerwacje w MerlinX / MDSWS

Robocza dokumentacja rezerwacji budowana z eksperymentów i oficjalnej dokumentacji API MerlinX.

Priorytet źródeł dla tego dokumentu:

1. eksperymenty i debugowanie realnego flow rezerwacyjnego po API v4 / BookV4
2. dokumentacja MDSWS / BookV4
3. payloady i zachowanie portalu MerlinX B2B
4. MWS API v5 tylko jako pomoc semantyczna tam, gdzie nie zaciemnia obrazu rezerwacji

## Zakres i zasady tego dokumentu

- Ten dokument ma opisywać rezerwacje na podstawie:
  - naszych eksperymentów i debugowania,
  - oficjalnej dokumentacji MDSWS / BookV4,
  - payloadów i zachowania portalu MerlinX B2B,
  - pomocniczo MWS API v5, jeżeli doprecyzowuje znaczenie pól, ale nie prowadzi do błędnych założeń integracyjnych.
- Obecny kod rezerwacji w repo jest uznany za niewiarygodny i nie jest źródłem prawdy dla tego dokumentu.
- Każdy punkt powinien dać się zakwalifikować do jednej z trzech grup:
  - `Potwierdzone`: wynika wprost z dokumentacji API albo z powtarzalnego eksperymentu,
  - `Wniosek roboczy`: mocna interpretacja, ale jeszcze nie domknięta eksperymentem,
  - `Do sprawdzenia`: pytanie otwarte.

## Aktualna strategia integracyjna

### Potwierdzone

- Dla flow rezerwacyjnego będziemy używać endpointów API v4 / BookV4.
- MWS API v5 w obszarze rezerwacji jest dla nas zbyt niejasne, żeby budować na nim integrację produkcyjną.
- MWS API v5 może nadal pomagać jako materiał pomocniczy do interpretacji semantyki pól i statusów, ale nie jest dla nas źródłem prawdy dla samego flow rezerwacyjnego.

## Słownik pojęć, żeby nie mieszać bytów

### Potwierdzone

- Pierwszym krokiem flow rezerwacyjnego w naszym produkcie jest zebranie danych kontaktowych klienta.
- Zbieranie danych kontaktowych klienta jest krokiem poprzedzającym pierwszy request do MerlinX i stanowi wejście do flow rezerwacyjnego.
- Zakres danych w tym pierwszym kroku jest taki sam jak w aktualnym formularzu kontaktowym i na dziś jest już ustalony.
- Drugim krokiem flow rezerwacyjnego jest odpytanie API v4 / BookV4 o stan oferty i formularza rezerwacyjnego, czyli:
  - `POST http://mdsws.merlinx.pl/bookV4/check`.
- `bookV4/check` zwraca pola formularza rezerwacyjnego oraz aktualny stan oferty potrzebny do dalszego kroku.
- Request `bookV4/check` ma typ:
  - `<type>check</type>`.
- W `conditions` dla `bookV4/check` podajemy co najmniej:
  - `language`,
  - `currency`,
  - `ofr_tourOp`,
  - `ofr_id`,
  - `par_adt`,
  - `par_chd`,
  - `par_inf`.
- W `forminfo` już na etapie `check` można przekazać listę uczestników w `<persons>`.
- `forminfo` rozdziela dwa osobne byty:
  - `persons.person` <Person[]> jako listę wszystkich uczestników rezerwacji,
  - `client` <Person> jako dane klienta składającego i opłacającego rezerwację.
- Biznesowo `client` nie musi być jednym z uczestników z `persons` i nie traktujemy takiego przypadku w żaden szczególny sposób.
- W badanym flow `ofr_id` nie zawiera pełnego composed offer id, tylko główną część z identyfikatora:
  - z `mainOfferId|OPER|base64(paxMeta)` do `<ofr_id>` trafia tylko `mainOfferId`.
- Płeć uczestnika jest kodowana jako:
  - `H` (niem. Herr) dla płci męskiej,
  - `F` (niem. Frau) dla płci żeńskiej.
- Liczba uczestników przekazanych w `persons` musi zgadzać się z konfiguracją uczestników, dla której zostało wygenerowane id oferty.
- W praktyce oznacza to, że `paxMeta` z composed offer id i payload `par_adt` / `par_chd` / `par_inf` oraz osoby wymienione w `persons` muszą być ze sobą spójne.
- Przykładowy request startowy `bookV4/check`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<mds>
  <auth>
    <login>...</login>
    <pass>...</pass>
    <source>MDSWS</source>
    <srcDomain>skionline.pl</srcDomain>
    <consultant>...</consultant>
  </auth>
  <request>
    <type>check</type>
    <conditions>
      <language>PL</language>
      <currency>PLN</currency>
      <ofr_tourOp>NKRA</ofr_tourOp>
      <ofr_id>mainOfferId</ofr_id>
      <par_adt>2</par_adt>
      <par_chd>0</par_chd>
      <par_inf>0</par_inf>
    </conditions>
  </request>
</mds>
```

- Dokumentacja MDSWS `bookingv4:fields` definiuje dla pól formularza atrybuty:
  - `required` jako wymagane dla bookingu,
  - `requiredOption` jako wymagane dla rezerwacji opcyjnej.
- Oznacza to, że obowiązkowość pól nie jest opisana jako jedna stała reguła przy samym endpointcie `check`, tylko jest zwracana przez API w strukturze formularza.
- Dokumentacja BookV4 rozróżnia dalej typy rezerwacji:
  - `book` oznacza rezerwację definitywną / stałą,
  - `optionbooking` oznacza rezerwację opcyjną,
  - `optionconfirm` oznacza potwierdzenie wcześniej założonej rezerwacji opcyjnej.
- MWS API v5 opisuje kompatybilne statusy i identyfikację rezerwacji pomocniczo, ale nie jest głównym torem integracji samego flow.
- Statusy rezerwacji opisane w specyfikacji `v5` / kompatybilnym `v4.1`:
  - `OP` = rezerwacja opcyjna,
  - `OQ` = offer to pay, non-blocking seats option,
  - `RF` = potwierdzona rezerwacja opcyjna,
  - `OK` = rezerwacja definitywna / firm booking,
  - `RQ` = rezerwacja na zapytanie,
  - `RR` = zapytanie potwierdzone przez organizatora,
  - `XX` = rezerwacja anulowana,
  - `??` = status nieustalony technicznie.
- Z eksperymentów roboczych wynika, że rezerwacja quota po założeniu ma status `OQ`
- `Base.Availability.quota.possible` i `booking status` są ze sobą powiązane, ale dotyczą różnych etapów flow:
  - `Base.Availability.quota.possible` mówi, czy dla sprawdzanej oferty da się założyć rezerwację typu quota,
  - `booking status` opisuje już stan założonej rezerwacji.
  Innymi słowy: to nie są dwa niezależne byty biznesowe, tylko dwa różne punkty obserwacji tego samego typu flow rezerwacyjnego.
  W specyfikacji są modelowane osobno, bo jedno pole dotyczy etapu availability / form check, a drugie etapu istniejącej rezerwacji.
- Żeby sprawdzić poprawność formularza rezerwacyjnego wysyłamy pola formularza w sekcji `request.forminfo` payloadu pod endpoint `bookV4/check`. Przy pierwszym pobraniu formularza, sekcję `forminfo` można zupełnie pominąć, a API zwróci czysty formularz dla uczestników zdefiniowanych w polach `par_adt`, `par_chd`, `par_inf`.

### Wniosek roboczy

- Najbezpieczniejsza interpretacja dokumentacji MDSWS jest taka:
  - `bookV4/check` wolno inicjować z pustym `<client />`,
  - ale to odpowiedź `check` decyduje, które pola `client` trzeba potem uzupełnić dla `book` albo `optionbooking`.

### Do sprawdzenia

- Jak dokładnie wygląda przejście między:
  - `Base.Availability.quota.possible = true`,
  - faktycznym założeniem rezerwacji,
  - statusem `OQ`.

## Pierwsze ustalenia z eksperymentów

### Potwierdzone

- Rezerwacja definitywna od razu kupuje bilety lotnicze i inne elementy, więc może generować koszty, których nie da się cofnąć. Tę operację trzeba traktować jako nieodwracalną i **nie wolno** jej używać do testów "na żywo".
- Usługi dodatkowe, ubezpieczenia, bagaż itd. należy wybierać w momencie rezerwowania i przesyłać jako część formularza rezerwacyjnego.
- Numer rezerwacji wysłany do użytkownika można wykorzystać do sprawdzenia statusu rezerwacji razem z kodem operatora zgodnym z kodami MerlinX pod endpointem v5 `GET /v5/booking/status?Booking.Id=<id-rezerwacji>&Booking.Operator=<kod-operatora>`. Endpoint v4 to `POST /bookV4/bookingstatus` z payloadem `request.conditions.booking_number`, `request.conditions.ofr_tourOp`.

### Wniosek roboczy

- Jeżeli w UI chcemy pokazać dodatki użytkownikowi, to musimy traktować je jako część właściwego flow rezerwacyjnego, a nie jako późniejszy "upsell po rezerwacji". Choćby dlatego, że jako upsell są droższe, a i sama formuła upsell jest bardziej problematyczna dla użytkownika.

## Co dokumentacja mówi o quota i zgodach formalnych

### Potwierdzone

- Nie znaleźliśmy literalnego pola ani nazwy `GDPRAccepted` w lokalnej dokumentacji MDSWS ani na oficjalnych stronach `bookingv4:book`, `bookingv4:fields`, `/quota`.
- Oficjalna dokumentacja `bookingv4:book` wymienia typy requestu:
  - `book`,
  - `optionbooking`,
  - `optionconfirm`,
  - `bookingchangecheck`,
  - `bookingchange`.
- Oficjalna dokumentacja `bookingv4:book` nie wymienia `quota` jako typu requestu dla `POST /bookV4/book`.
- Lokalna lista endpointów MDSWS zawiera osobny endpoint:
  - `/quota`.
- Oficjalna strona dokumentacji online pod adresem `https://documdsws.merlinx.pl/quota` obecnie nie zawiera treści merytorycznej, tylko komunikat `This topic does not exist yet`.
- Udokumentowany mechanizm zgód klienta w BookV4 to:
  - `forminfo.formalAgreements.formalAgreement`.
- Udokumentowane pola `formalAgreement` to:
  - `required`,
  - `code`,
  - `selected`,
  - `desc`.
- Dokumentacja pokazuje, że akceptacja zgody jest wysyłana przez:
  - `formalAgreement.code`,
  - `formalAgreement.selected = 1`.
- Dokumentacja i przykłady pokazują, że zgody o charakterze RODO / marketing / przetwarzanie danych mogą występować właśnie jako `formalAgreements` z kodami operator-specific, a nie jako jedno globalne pole typu `GDPRAccepted`.
- W starszej dokumentacji MDSWS temat zgód był modelowany przez osobne pola:
  - `conditions`,
  - `ext_conditions`,
  - `marketing_condition`,
  - `skok_condition`.

### Wniosek roboczy

- Błąd `GDPRAccepted required for quota` najpewniej oznacza brak zaakceptowania wymaganej zgody formalnej, ale backend nazywa ten warunek wewnętrznie `GDPRAccepted`, a nie nazwą pola z kontraktu BookV4.
- Najbardziej prawdopodobny nośnik tej zgody w BookV4 to `forminfo.formalAgreements`, a nie osobne pole XML o nazwie `GDPRAccepted`.
- Jeżeli rzeczywiście wysyłamy `POST /bookV4/book` z `request.type = quota`, to poruszamy się po zachowaniu, którego oficjalna dokumentacja `bookingv4:book` nie opisuje wprost.
- To tłumaczy, dlaczego błąd backendu może odwoływać się do pojęć, których nie ma w publicznym opisie `bookV4/book`.

### Do sprawdzenia

- Czy quota powinna być finalnie wywoływana przez osobny endpoint `/quota`, a nie przez `POST /bookV4/book` z `type = quota`.
- Czy dla operatora i oferty z eksperymentu `bookV4/check` powinno w pewnych warunkach zwrócić `formalAgreements`, mimo że w obecnej próbce ich nie zwróciło.
- Czy quota wymaga wysłania minimalnego zestawu zgód / warunków nawet wtedy, gdy odpowiedź `check` ich nie pokazuje.

## Wymagania implementacyjne / A-C z ustaleń operacyjnych

Ta sekcja porządkuje ustalenia biznesowe z rozmów operacyjnych. To są nasze robocze acceptance criteria dla implementacji, nawet jeżeli część z nich jest operator-specific i nie wynika bezpośrednio z kontraktu API.

### Potwierdzone

- `Hierarchia trybów`: jeżeli da się założyć opcję, wybieramy zawsze opcję jako pierwszy wybór.
- `Hierarchia trybów`: jeżeli opcji nie ma, ale dostępna jest quota, wybieramy quotę zamiast rezerwacji stałej.
- `Hierarchia trybów`: jeżeli nie ma ani opcji, ani quoty, a dostępna jest tylko rezerwacja stała, taki przypadek również obsługujemy automatycznie na stronie.
- `Hierarchia trybów`: preferencja biznesowa między trybami to:
  - `opcja > quota > stała`.
- `Dokumenty przed płatnością`: dla flow opcji i quoty zespół preferuje najpierw wygenerowanie dokumentów rezerwacyjnych, a dopiero potem wpłatę klienta.
- `Pierwszy krok UX`: przed odpytaniem `bookV4/check`, trzeba zebrać od klienta dane kontaktowe zgodnie z aktualnym flow rezerwacyjnym.
- `Pierwszy krok UX`: zebranie danych kontaktowych jest pierwszym krokiem rezerwacji po stronie produktu, nawet jeśli pierwszy request do MerlinX następuje dopiero później.
- `Pierwszy krok UX`: zakres pól tego kroku pozostaje taki jak w aktualnym formularzu i nie wymaga nowej decyzji biznesowej.
- `Rezerwacje na zapytanie`: oferty na zapytanie powinny zostać przy formularzu kontaktowym, a nie przy flow natychmiastowej płatności i "pewnej" rezerwacji.
- `Opcja`: rezerwacja opcjonalna blokuje miejsca, działa do wskazanego terminu i może być do tego czasu bezkosztowo anulowana.
- `Opcja`: długość opcji zależy od operatora i terminu wyjazdu; może to być np. `24h`, `12h` albo `2h`.
- `Opcja`: rezerwacje opcjonalne muszą być zawsze potwierdzane ręcznie albo programowo; sam fakt wpłaty klienta nie wystarczy bez potwierdzenia rezerwacji w Merlinie.
- `Opcja`: rezerwacje opcjonalne same się anulują w Merlinie, jeżeli nie zostaną potwierdzone na czas.
- `Opcja`: przy bliskich terminach, np. w Nekerze, opcja może trwać tylko `2h`, więc nie można polegać na ręcznej obsłudze dopiero następnego dnia roboczego.
- `Quota`: quota nie blokuje miejsc, ale generuje dokumenty rezerwacyjne i mail do klienta.
- `Quota`: w znanym flow Itaki quota automatycznie generuje mail do klienta z linkiem do wpłaty wymaganej kwoty.
- `Quota`: w tym flow nie widzimy treści maila od Itaki i nie mamy możliwości jej edycji.
- `Quota`: w znanym flow quoty klient płaci według instrukcji organizatora, a nie przez nasz własny checkout.
- `Quota`: po opłaceniu przez klienta quota przekształca się w rezerwację stałą, ale operacyjnie nadal trzeba ją potwierdzić ręcznie.
- `Quota`: quota jest preferowana nad rezerwacją stałą wtedy, gdy opcja nie jest dostępna, bo przynajmniej generuje dokumenty dla klienta.
- `Rezerwacja stała`: nie wolno jej traktować jako bezpiecznego domyślnego fallbacku, bo od razu może generować nieodwracalne koszty.
- `Rezerwacja stała`: jeżeli jest jedynym dostępnym trybem, obsługujemy ją automatycznie na stronie.
- `Rezerwacja stała`: dla przypadku `tylko stała` oczekujemy najpierw wpłaty, a dopiero potem założenia rezerwacji stałej.
- `Rezerwacja stała`: jeżeli po pobraniu wpłaty nie uda się już założyć rezerwacji, akceptowany scenariusz biznesowy to zwrot środków klientowi.
- `Rezerwacja stała`: klient musi być wyraźnie poinformowany, że w tym wariancie pobranie płatności może poprzedzać samo skuteczne założenie rezerwacji.
- `Rezerwacja stała`: komunikat dla klienta w wariancie `tylko stała` ma brzmieć:

```text
Dla tej oferty możliwa jest tylko rezerwacja ostateczna. W tym celu najpierw pobierzemy należność, a następnie zgłosimy rezerwację do operatora. Jeżeli operator odrzuci rezerwację, to zwrócimy Państwu całą zapłaconą kwotę. W razie pytań zapraszamy do kontaktu telefonicznego.
```

- `Rezerwacja stała`: dla wdrożenia na stronie, jeżeli mamy techniczną kontrolę nad kolejnością działań, to dla **rezerwacji stałych** lub **potwierdzenia rezerwacji opcyjnej** lub **potwierdzenia quoty**, a więc zawsze przy utworzeniu ostatecznej rezerwacji, bez względu na poprzednie kroki, preferowana kolejność jest następująca:
  - najpierw pobranie wpłaty,
  - potem próba założenia rezerwacji stałej / potwierdzenia / przejścia.
- `Daleki termin`: operacyjnie oznacza taki przypadek, w którym operator dopuszcza zaliczkę zamiast natychmiastowej płatności całości.
- `Płatność`: część kwoty pobieramy tylko wtedy, gdy termin i warunki operatora pozwalają na zaliczkę zamiast pełnej płatności.
- `Płatność`: dla Itaki i Nekery Merlin pokazuje wysokość zaliczki i termin dopłaty do całości.
- `Płatność`: zaliczki są operator-specific i mogą się zmieniać wraz z regulaminami lub etapami promocji.
- `Płatność`: standardowa zaliczka to zwykle `25-30%`, ale nie wolno tego hardcode'ować jako reguły uniwersalnej.
- `Zwroty`: jeżeli pobraliśmy wpłatę, ale nie udało się założyć lub potwierdzić rezerwacji, zwrot wykonuje backoffice ręcznie.
- `Zwroty`: system ma w takiej sytuacji wygenerować i wysłać email do backoffice z informacją o zajściu.
- `Zwroty`: ta sama polityka obowiązuje dla:
  - `tylko stała`,
  - nieudanego potwierdzenia opcji po wpłacie,
  - problemu po opłaconej quocie.
- `Snowtrex`: zaliczkę `25%` trzeba wyliczać po naszej stronie.
- `Snowtrex`: pozostała kwota jest płatna `6 tygodni` przed wyjazdem.
- `Snowtrex`: jeżeli rezerwacja jest zakładana w okresie `6 tygodni` przed wyjazdem, trzeba pobrać od razu całość.
- `Płatność`: w flow opcji i quoty nie pobieramy pieniędzy przed wygenerowaniem dokumentów rezerwacyjnych.
- `Snowtrex`: dostępność i rezerwację w Merlinie odpytujemy jak dla samych dorosłych, nawet jeżeli faktycznie jadą dzieci.
- `Snowtrex`: po założeniu takiej rezerwacji zespół pisze mail do Snowtrex z korektą dat urodzenia i ceny.
- `Snowtrex`: prawidłowe daty urodzenia trzeba mimo to zebrać od klienta, bo są potrzebne ze względu na ubezpieczenie zawarte w cenie.
- `Snowtrex`: obejście z dziećmi ma być dla klienta transparentne; formularz ma zebrać prawdziwe dane, a obsługa wewnętrzna ma dostać komplet informacji do ręcznej korekty.
- `Snowtrex`: wewnętrzne powiadomienie o rezerwacji ma wyraźnie oznaczać, że to przypadek Snowtrex wymagający ręcznej korekty po stronie operatora.
- `Backoffice mail`: każda rezerwacja od etapu po wypełnieniu formularza kontaktowego skutkuje wysłaniem maila do backoffice.
- `Backoffice mail`: taki mail zawiera co najmniej:
  - operatora,
  - uczestników,
  - informacje o rezerwacji.
- `Snowtrex`: w Snowtrex zakłada się zawsze rezerwację stałą.
- `Snowtrex`: jeżeli klient nie wpłaci, mamy `24h` na anulację takiej rezerwacji. Tę anulację przeprowadza backoffice ręcznie.
- `Snowtrex`: w tym wariancie akceptujemy ryzyko, że po wpłacie nie uda się już założyć rezerwacji; wtedy środki trzeba zwrócić klientowi.
- `Snowtrex`: klient musi dostać jasną informację o takim ryzyku w UI.
- `Snowtrex`: dodatkowe opcje typu skipassy i sprzęt narciarski nie są widoczne w Merlinie; jeżeli klient czegoś takiego chce, zespół załatwia to indywidualnie ze Snowtrex.
- `Snowtrex + dzieci`: w mailu do backoffice obok listy uczestników trzeba dopisać pogrubioną informację dla pracowników, np.:

```text
To jest oferta od operatora Snowtrex. Automatyczna rezerwacja została złożona na N uczestników dorosłych. Należy ręcznie zmienić warunki rezerwacji w porozumieniu z operatorem, tak aby zmienić dane uczestników z N dorosłych na A dorosłych i B dzieci.
```
- `Dodatki`: klient musi być poinformowany, że bagaż i transfer mogą nie być zawarte w cenie.
- `Dodatki`: klient musi być poinformowany, że niektóre usługi są droższe, jeżeli doda się je później.
- `Dodatki`: klient musi być poinformowany, że część usług, np. `GNC` lub ubezpieczenie od kosztów rezygnacji, można dodać tylko przy zakładaniu rezerwacji.
- `Dodatki`: usługi dodatkowe trzeba pokazywać i zbierać dotyczące ich wybory klienta w samym flow rezerwacyjnym.
- `Operatorzy`: każdy organizator ma inne warunki zakładania rezerwacji, inne zaliczki i inne usługi dodatkowe, więc implementacja musi być operator-specific, a nie oparta na jednym sztywnym scenariuszu.
- `Itaka`: przy bliskim terminie, ostatnim pokoju albo ofercie `SMART` nie da się założyć opcji; wtedy zakłada się quotę.
- `Itaka`: dopiero w momencie rezerwacji do ceny dochodzi opłata `TFG/TFP`.
- `Itaka`: w momencie rezerwacji Itaka automatycznie dodaje ubezpieczenie `rezygnacja cov plus`, którego większość klientów nie chce, my resetujemy wszystkie automatyczne zaznaczenia ubezpieczeń i dodatków, żeby klient mógł świadomie wybrać, co chce.
- `Itaka SMART`: ofert opartych na tanich liniach nie da się wiarygodnie odfiltrować wyłącznie przez Merlin; potrafią mieszać się z czarterami i nie zawsze są jawnie oznaczone jako `SMART`.
- `Itaka SMART`: operacyjnie rozpoznawanie takich ofert wymaga sprawdzania informacji o przewoźniku, a czasem także dodatkowej weryfikacji na stronie Itaki.
- `Itaka SMART`: domyślnie nie wolno komunikować klientowi niepewnej klasyfikacji typu „może to być SMART”. Najpierw trzeba spróbować ustalić ten status wystarczająco pewnie.
- `Itaka SMART`: klasyfikację SMART trzeba próbować rozwiązać programistycznie:
  - dodatkowymi zapytaniami do MerlinX,
  - a jeżeli to nie wystarczy, także dodatkowymi źródłami po stronie operatora / przewoźnika / czarterowni.
- `Itaka SMART`: dopiero jeżeli mimo najwyższych starań nie uda się sklasyfikować oferty wystarczająco pewnie, dopuszczamy komunikat do klienta, że oferta może mieć cechy SMART / taniej linii i związane z tym ograniczenia.
- `Itaka SMART`: klient musi być poinformowany o skutkach oferty opartej na tanich liniach:
  - samodzielna odprawa w aplikacji `24h` przed wylotem i przed powrotem,
  - brak bagażu rejestrowanego,
  - transfery organizowane przez firmy lokalne,
  - brak rezydenta.
- `Nekera`: oferty oparte na czarterach można filtrować przez warunek `lot bezpośredni i bagaż w cenie`.
- `Nekera City Break`: transfery nie są w cenie, ale w momencie rezerwacji można dokupić m.in. transfer, `speedy boarding`, bagaż `10 kg`, bagaż `20 kg` i ubezpieczenie od rezygnacji.
- `Zasięg wyjątku tylko-stała`: według aktualnej wiedzy backoffice nie spotkał poza Snowtrex przypadku, w którym nie byłoby ani opcji, ani quoty; w Nekerze opcja wydaje się dostępna praktycznie do dnia przed wyjazdem, a w Itace w takich sytuacjach występuje quota.
- `Domyślne pola`: z ustaleń biznesowych nie wynika potrzeba domyślnego zaznaczania albo wypełniania pól za klienta.

### Wniosek roboczy

- Najbardziej spójna reguła implementacyjna na dziś wygląda tak:
  - `opcja > quota > stała`,
  - ale `stała` jest trybem ostatniego wyboru i wymaga innego podejścia do ryzyka oraz do momentu pobrania płatności.
- Dla trybu `tylko stała` obecna decyzja biznesowa jest taka:
  - lepiej zaryzykować, że rezerwacja nie założy się po wpłacie,
  - niż założyć stałą rezerwację i ryzykować brak wpłaty klienta.
- Dla operatorów typu Snowtrex trzeba zaakceptować, że część procesu jest z natury hybrydowa:
  - formularz i płatność zbierają dane online,
  - ale korekta składu uczestników i części usług żyje poza Merlinem.
- Dla dodatków i harmonogramu płatności frontend nie może udawać stabilnej, jednorodnej logiki między operatorami, bo z rozmów jasno wynika, że te reguły różnią się nie tylko między operatorami, ale nawet między promocjami tego samego operatora.
- Dla `SMART` pierwszym wyborem musi być rozstrzygnięcie programistyczne. Ostrożny komunikat z niepewnością jest dopuszczalny tylko jako fallback po wykorzystaniu wszystkich sensownych prób klasyfikacji.

### Do sprawdzenia przed wdrożeniem

- Czy quota również ma być automatycznie potwierdzana przez nasz system po wykryciu wpłaty, jeżeli znajdziemy wiarygodny sygnał takiej wpłaty po stronie API lub mailingu operatora.
- Czy quota po wpłacie klienta do organizatora potwierdza się sama po stronie operatora, czy wymaga jeszcze naszej akcji; obecna hipoteza biznesowa jest taka, że organizator domyka to sam po otrzymaniu wpłaty, ale trzeba to potwierdzić eksperymentalnie.
- Czy dla ograniczeń niewidocznych w Merlinie, np. `Snowtrex skipass / sprzęt`, chcemy dać klientowi choćby pole na zgłoszenie zainteresowania do ręcznej obsługi, czy świadomie tego nie pokazujemy.
- Czy analogiczne świadome potwierdzenie chcemy też dla trybu `tylko stała`, gdzie istnieje ryzyko pobrania wpłaty przed skutecznym założeniem rezerwacji.
- Jak technicznie wykrywać oferty `SMART` na tyle wiarygodnie, żeby ostrzeżenia klienta nie opierały się wyłącznie na ręcznej pracy zespołu.

## Wnioski z payloadu formularza Merlin B2B

Ta sekcja jest pomocnicza. Dotyczy payloadu `form-data` wysyłanego przez portal MerlinX B2B i nie jest publicznym kontraktem MWS API v5. Trzeba ją traktować jako materiał do dedukcji semantyki, nie jako oficjalną specyfikację integracyjną.

Źródło próbki:

- `docs/reservations/merlin-b2b-form-payload.json`

### Potwierdzone

- Plik nie jest zwykłą mapą `field -> value`, tylko śladem kolejności `form-data` w postaci:
  - `"pole,wartość" -> indeks kolejności`.
- W badanej próbce quota payload zawiera jednocześnie:
  - `ReservationMode=0`,
  - `ReservationQuota=1`,
  - `check_price=0`,
  - `check_payment_offer=0`,
  - `expectedPrice=3770.00`,
  - `prepayment[...]`.
- Zestaw `ReservationMode=0` + `ReservationQuota=1` pokazuje, że quota nie jest zakodowana samym `ReservationMode`.
- W starszej dokumentacji MDSWS `ReservationMode=0` oznacza rezerwację opcjonalną, a `ReservationMode=1` rezerwację normalną / definitywną.
- W payloadzie występuje `fullOfrId`, które niesie pełny identyfikator oferty razem z operatorem.
- `fullOfrId` z payloadu B2B wygląda spójnie z composed offer id, ale dla `bookV4/check` do `<ofr_id>` trafia tylko jego główna część (`mainOfferId`).
- W payloadzie występuje 15 bloków `opt_service[...]`, czyli portal odsyła szeroki stan formularza usług dodatkowych, a nie tylko czyste dane klientów.
- Same bloki `opt_service[...]` nie zawierają w tej próbce jawnego pola typu `checked=1`, więc sama obecność bloku nie dowodzi jeszcze wyboru usługi przez użytkownika.

### Wniosek roboczy

- Najmocniejsza dedukcja z tej próbki jest taka:
  - `ReservationMode` odpowiada raczej za podstawowy tryb rezerwacji typu "definitywna vs niedefinitywna / opcjonalna",
  - `ReservationQuota` jest dodatkową flagą, która uszczegóławia ten tryb do wariantu quota.
- Innymi słowy: quota wygląda tu jak osobny przełącznik nałożony na rezerwację niedefinitywną, a nie jak wartość zakodowana bezpośrednio w `ReservationMode`.
- `check_price=0` najpewniej oznacza, że ten konkretny submit nie ma uruchamiać dodatkowego przeliczenia ceny.
- `check_payment_offer=0` najpewniej oznacza, że ten submit nie ma uruchamiać dodatkowego przeliczenia / sprawdzenia oferty płatności.
- Powyższe dwie interpretacje pasują do tego, że payload niesie już:
  - `expectedPrice`,
  - ceny per osoba,
  - pola `prepayment[...]`.
- To sugeruje, że payload jest wysyłany już po wcześniejszym ustaleniu ceny i harmonogramu płatności, a nie jako request stricte przeliczający.

### Do sprawdzenia

- Czy dla zwykłej rezerwacji opcyjnej portal wysyła:
  - `ReservationMode=0`,
  - `ReservationQuota=0`.
- Czy dla rezerwacji definitywnej portal wysyła:
  - `ReservationMode=1`,
  - `ReservationQuota=0`.
- Co dokładnie zmienia `check_price=1`, jeżeli uda się złapać próbkę z taką wartością.
- Co dokładnie zmienia `check_payment_offer=1`, jeżeli uda się złapać próbkę z taką wartością.
- Jaki jest jawny sygnał wyboru usługi dodatkowej w payloadzie portalu:
  - osobne pole `checked`,
  - brak / obecność konkretnego bloku,
  - zmiana `srvAlloc`,
  - jeszcze inne pole niewidoczne w tej próbce.

## Co dokumentacja API mówi o dodatkach

### Potwierdzone

- W MWS API v5 dodatki są modelowane jako `AdditionalServices`.
- W `bookingFormRequest` v5 wybrane dodatki są wysyłane jako `AdditionalServices`.
- Oferta i odpowiedzi formularza w v5 mogą zawierać:
  - `AdditionalServices`,
  - `Payment.Schedule`.
- W dokumentacji pomocniczej BookV4 / MDSWS ten sam obszar występuje pod starszymi nazwami:
  - `services_optional`,
  - `add_service`.
- To oznacza, że znaczenie jest zbliżone, ale shape zależy od warstwy API i nie wolno przenosić nazw 1:1 bez zaznaczenia, czy mówimy o v5 czy o starym BookV4.
- Dokumentacja starszego formularza rezerwacji ostrzega, że wybranie usługi dodatkowej może:
  - zmienić cenę całkowitą,
  - zmienić sekcję płatności i harmonogram płatności.
- W tej samej dokumentacji jest zalecenie, aby po wybraniu usług dodatkowych ponownie wykonać `check`, żeby przeliczyć cenę i płatności przed finalnym bookowaniem.

### Wniosek roboczy

- Minimalny bezpieczny flow dla dodatków w v5 wygląda tak:
  - pobierz `GET /v5/booking/form`,
  - pokaż `AdditionalServices`,
  - wyślij wybrane dodatki przez `POST /v5/booking/formcheck`,
  - dopiero po ponownym przeliczeniu ceny i płatności przechodź do faktycznego założenia / potwierdzenia rezerwacji.

### Do sprawdzenia

- Którzy operatorzy zwracają dodatki per osoba, a którzy per rezerwacja.
- Które dodatki są wzajemnie wykluczające i jak to jest oznaczone w odpowiedzi.
- Czy każdy operator zwraca dodatki już na etapie `check`, czy część dopiero na późniejszym etapie.

## Co dokumentacja API mówi o statusie rezerwacji

### Potwierdzone

- W MWS API v5 status rezerwacji sprawdza się przez `GET /v5/booking/status`.
- Endpoint v5 identyfikuje rezerwację przez:
  - `Booking.Id`,
  - `Booking.Operator`.
- Obiekt statusu v5 zawiera co najmniej:
  - `Id`,
  - `Operator`,
  - `Status`,
  - opcjonalnie `Message`,
  - opcjonalnie `Price`.
- W kompatybilnym `v4.1` odpowiednikiem są `bookingstatusshort` / `bookingstatus`, które dodatkowo pokazują bogatsze sekcje jak `payment`, `forminfo` czy `hints`.
- W MWS API v5 płatności są modelowane strukturalnie jako `Payment.Schedule`.
- Numer rezerwacji wysłany do użytkownika odpowiada bezpośrednio `Booking.Id` w MWS API v5.

### Wniosek roboczy

- Skoro status rezerwacji da się odpytać po identyfikatorze rezerwacji i operatorze zarówno w v5, jak i w kompatybilnym v4.1, to numer z maila do użytkownika rzeczywiście wygląda na istotny identyfikator operacyjny po stronie MerlinX.

## Link do organizatora lub płatności

### Potwierdzone

- W przejrzanej dokumentacji MWS API v5 nie znaleźliśmy jawnie udokumentowanego pola typu `paymentUrl`, `paymentLink` ani osobnego pola z linkiem do strony organizatora.
- W kompatybilnej dokumentacji `v4.1` / MDSWS również nie znaleźliśmy takiego jawnego pola.
- W przykładach odpowiedzi występują inne tropy, ale nie są one opisane jako link płatniczy:
  - `hints` może zawierać np. `basket id`,
  - `detailsData` może zawierać `descUrl`, które wygląda raczej jak link do szczegółów oferty niż do płatności.

### Wniosek roboczy

- Na dziś nie wolno zakładać, że MWS v5 albo kompatybilne v4.1 zwrócą gotowy link do płatności albo do strony organizatora.
- Ten link może:
  - przychodzić tylko mailem od operatora,
  - pochodzić z innego endpointu,
  - być zaszyty w polu, które nie jest jawnie opisane jako URL płatności,
  - zależeć od operatora.

### Do sprawdzenia

- Czy odpowiedź API po przypadku biznesowo nazywanym "quota" zawiera link do strony organizatora.
- Czy jeżeli taki link istnieje, prowadzi do:
  - szczegółów oferty,
  - koszyka / basket,
  - bezpośrednio do płatności.
- W którym dokładnie endpointcie ten link pojawia się po raz pierwszy:
  - odpowiedź `book`,
  - odpowiedź `optionbooking`,
  - odpowiedź `optionconfirm`,
  - `bookingstatusshort`,
  - `bookingstatus`,
  - mail operatora.

## Ryzyka operacyjne

### Potwierdzone

- Rezerwacja definitywna jest operacją wysokiego ryzyka kosztowego.
- Dodatki wpływają na cenę i płatności, więc nie można ich traktować jako neutralnej kosmetyki formularza.

### Wniosek roboczy

- Każdy eksperyment na produkcyjnym operatorze powinien zaczynać się od flow opcyjnego albo środowiska testowego. Rezerwacja definitywna powinna być uruchamiana dopiero wtedy, gdy:
  - znamy dokładny expected outcome,
  - mamy kontrolę nad danymi testowymi,
  - wiemy, kto ponosi koszt nieodwracalnej operacji.

## Zalecane kolejne eksperymenty

1. Złapać surową odpowiedź XML dla przypadku, który biznesowo nazywacie "quota", i przypisać ją do konkretnego request type / statusu.
2. Złapać surową odpowiedź XML po `optionbooking`, `optionconfirm` i `book` dla tej samej klasy oferty i porównać:
   - status,
   - numer rezerwacji,
   - sekcję `payment`,
   - `hints`,
   - ewentualne pola wyglądające na linki.
3. Porównać mail wysyłany do użytkownika z odpowiedziami `bookingstatusshort` i `bookingstatus` dla tego samego `booking_number`.
4. Sprawdzić eksperymentalnie, czy wybranie dodatku zmienia:
   - `pricetotal`,
   - sekcję `payment`,
   - wymagane pola w `forminfo`.
5. Zbudować katalog przykładów operator-specyficznych:
   - operator,
   - request type,
   - status,
   - czy są dodatki,
   - czy jest link,
   - czy powstaje koszt nieodwracalny.

## Źródła

- Lokalne pliki dokumentacyjne w repo:
  - `docs/api/api-v5.yml`
  - `docs/api/MerlinX MWS.htm`
  - `docs/api/Inquiry [MDSWS].txt`
  - `docs/api/Booking form [MDSWS].txt`
  - `docs/api/Checking availability and booking [MDSWS].txt`
  - `docs/api/responses/booking_v4_check_example*.xml`
  - `docs/api/MERLIN Web Services [MDSWS].htm`
- Oficjalne strony MDSWS wskazane przez lokalny indeks dokumentacji:
  - `https://docu.mdsws.merlinx.pl/bookingv4:book`
  - `https://docu.mdsws.merlinx.pl/bookingv4:optionconfirm`
  - `https://docu.mdsws.merlinx.pl/bookingv4:bookingstatusshort`
  - `https://docu.mdsws.merlinx.pl/bookingv4:bookingstatus`

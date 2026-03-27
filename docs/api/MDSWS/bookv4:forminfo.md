# bookV4 forminfo

## General rules

```xml
<address type="string" maxlength="30" name="Ulica" required="1" requiredOption="1">TESTOWA</address>
<country_code type="selected" maxlength="2" name="Kraj" required="1" requiredOption="1">
    <option value="AF">Afganistan</option>
</country_code>
```

Params:
| Element name | Attribute | Description |
| --- | --- | --- |
| address  <br>country_code | -  | Form field name |
| \*  | type | Field type. Possible types listed below. |
| \*  | maxlength | Maximum length in bytes |
| \*  | name | Field description. |
| \*  | required | Is field required for booking. |
| \*  | requiredOption | Is field required for optional booking. |
| \*  | dependOn | Field is depending on specific option selected.  <br>Example:  <br><br>dependOn="client_type:P"<br><br>  <br>Field will be shown only if field “`type`” inside “`client`” has value equal to `P` |

Field types:
| Type | Description |
| --- | --- |
| string | -  |
| integer | -  |
| postcode | -  |
| phone | Phone number |
| email | -  |
| date | Date in format: `DD.MM.YYYY` |
| selected | -  |
| checkbox | -  |

## services_optional

```xml
<service desc="Parking w Katowicach. Parking niestrzezony !!!"
         optionalServiceType="additional" perPassenger="1" perBooking="0" dateFromMin="2019-11-26T00:00:00"
         dateFromMax="2019-11-26T00:00:00" dateToMin="2019-12-04T00:00:00" dateToMax="2019-12-04T00:00:00"
         duration="8" status="??" total_price="0" currency="PLN">
    <debug/>
    <id>4</id>
    <code>PARK_KTW</code>
    <type>PAR</type>
    <codeElemType>M</codeElemType>
    <codeOptServType>A</codeOptServType>
    <packageType>NotSet</packageType>
    <group>6</group>
    <excludedCodes/>
    <requiredCodes/>
    <merlinGroupId>12</merlinGroupId>
    <merlinGroupName>Parkingi</merlinGroupName>
    <date_from>26.11.2019</date_from>
    <date_to>04.12.2019</date_to>
    <checked>0</checked>
    <flightData/>
    <allocations>
        <allocate type="select">
            <person>1</person>
            <value>1</value>
            <checked>0</checked>
            <options_list>
                <option value="0" currency="PLN" selected="1">Nie wybrano</option>
                <option value="1" price="59" currency="PLN">Wybrano 1</option>
                <option value="2" price="118" currency="PLN">Wybrano 2</option>
            </options_list>
        </allocate>
    </allocations>
    <prices>
        <price>
            <person>1</person>
            <price>59</price>
            <currency>PLN</currency>
        </price>
        <price>
            <person>2</person>
            <price>59</price>
            <currency>PLN</currency>
        </price>
    </prices>
    <hints/>
    <options/>
</service>
```

Params:
| Element name | Attribute | Description |
| --- | --- | --- |
| service | desc | -  |
| optionalServiceType | -  |
| perPassenger | -  |
| perBooking | -  |
| dateFromMin | -  |
| dateFromMax | -  |
| dateToMin | -  |
| dateToMax | -  |
| duration | -  |
| status | -  |
| total_price | -  |
| currency | -  |
| debug | -  | -  |
| id  | -  | -  |
| code | -  | -  |
| type | -  | -  |
| codeElemType | -  | -  |
| codeOptServType | -  | -  |
| packageType | -  | -  |
| group | -  | -  |
| excludedCodes | -  | -  |
| requiredCodes | -  | -  |
| merlinGroupId | -  | -  |
| merlinGroupName | -  | -  |
| date_from | -  | -  |
| date_to | -  | -  |
| checked | -  | -  |
| flightData | -  | -  |
| allocations | -  | -  |
| allocate | -  | -  |
| person | -  | -  |
| value | -  | -  |
| checked | -  | -  |
| options_list | -  | -  |
| prices | -  | -  |
| hints | -  | -  |
| options | -  | -  |

## wishes

equivalent in MerlinX:

[![](/_media/bookingv4:bookv4_forminfo_wishes.png)](/_media/bookingv4:bookv4_forminfo_wishes.png "bookingv4:bookv4_forminfo_wishes.png")

```xml
<wishes>
    <wishesList>
        <option value="" selected="1" description="Wybierz z listy"/>
        <option value="11780" selected="0" description="czajnik w pokoju"/>
        <option value="375" selected="0" description="pokój z balkonem"/>
        <option value="11777" selected="0" description="pokój z widokiem na morze"/>
    </wishesList>
</wishes>
```

## formalAgreements

equivalent in MerlinX:

[![](/_media/bookingv4:bookv4_forminfo_formalagreement.png)](/_media/bookingv4:bookv4_forminfo_formalagreement.png "bookingv4:bookv4_forminfo_formalagreement.png")

```xml
    <formalAgreements>
      <formalAgreement required="1">
        <code>uwagi_1</code>
        <selected>0</selected>
        <desc>Oświadczam, że przed zawarciem Umowy zostały przekazane Płatnikowi rezerwacji standardowe informacje Ustawy z dnia 24 listopada 2017.</desc>
      </formalAgreement>
      <formalAgreement required="0">
        <code>1</code>
        <desc>Zgadzam się na wykorzystywanie moich danych osobowych przez Exim S.A. w celach marketingowych, zgodnie z rozporządzeniem Parlamentu Europejskiego i Rady (UE) 2016/679 z dnia 27 kwietnia 2016 r. w sprawie ochrony osób fizycznych w związku z przetwarzaniem danych osobowych i w sprawie swobodnego przepływu takich danych oraz uchylenia dyrektywy 95/46/WE. &#13;
</desc>
      </formalAgreement>
      <formalAgreement required="0">
        <code>5</code>
        <desc>Zgadzam się na wykorzystywanie mojego adresu e-mail do przesyłania informacji handlowych, zgodnie z ustawą z dnia 18 lipca 2002 r. o świadczeniu usług drogą elektroniczną</desc>
      </formalAgreement>
      <formalAgreement required="0">
        <code>2</code>
        <desc>Wyrażam zgodę na wykorzystywanie moich danych osobowych niezbędnych do realizacji programu "NAJLEPSZE WAKACJE - NAJLEPSZY KLIENT", zgodnie z rozporządzeniem Parlamentu Europejskiego i Rady (UE) 2016/679 z dnia 27 kwietnia 2016 r. w sprawie ochrony osób fizycznych w związku z przetwarzaniem danych osobowych i w sprawie swobodnego przepływu takich danych oraz uchylenia dyrektywy 95/46/WE. </desc>
      </formalAgreement>
    </formalAgreements>
```

Params:
| Element name | Attribute | Description |
| --- | --- | --- |
| formalAgreement | -  | Formal agreement |
| formalAgreement | required | Is formal agreement required |
| code | -  | code used to identify agreement by Touroperator |
| selected | -  | Is formal agreement accepted by client. |
| desc | -  | Description to show to client. |

## paymentTable

## operPaymentTable

Section used for returning operator payment options

```xml
<operPaymentTable>
    <paymentItem id="cc" desc="Credit Card" amt="0" bookingType="direct" for="MAIN">
        <card_type type="selected" name="Rodzaj karty" required="1">
            <option value="VI">Visa</option>
            <option value="MC">MasterCard</option>
        </card_type>
        <card_owner type="card_owner" maxlength="30" name="Właściciel karty" required="1"/>
        <card_number type="card_number" maxlength="16" name="Karta kredytowa" required="1"/>
        <card_valid_mm type="card_valid_mm" maxlength="2" name="miesiąc" required="1"/>
        <card_valid_yy type="card_valid_yy" maxlength="2" name="rok" required="1"/>
        <card_cvv type="card_cvv" maxlength="3" name="CVV" required="1"/>
    </paymentItem>
    <paymentItem id="uw" desc="Bank Transfer." amt="0" bookingType="direct" for="MAIN,INSURANCE">
        <vgnr type="hidden">123456789</vgnr>
    </paymentItem>
</operPaymentTable>
```

Params:
| Element name | Attribute | Description |
| --- | --- | --- |
| paymentItem | -  | Single payment option item |
| paymentItem | id  | Id of payment option used to specify selected payment |
| desc | Description |
| amt | Payment additional cost |
| bookingType | -  |
| for | Comma separated list of items for which payment is carried.  <br>Possible options:  <br>`MAIN` - main trip offer  <br>`INSURANCE` - additional insurance  <br>`CAR` - car rental |
| card_type  <br>card_owner  <br>card_number  <br>card_valid_mm  <br>card_valid_yy  <br>card_cvv  <br>**\* (field list is dynamic, depending on Touroperator)** | type | Field type  <br>Possible options:  <br>string  <br>selected  <br>card_type  <br>card_owner  <br>card_number  <br>card_cvv  <br>card_valid_mm  <br>card_valid_yy  <br>img  <br>hidden |
| maxlength | Maximum field length (bytes) |
| name | Field description |
| required | Is field required for booking |
| requiredOption | Is field required for optional booking |

Selected payment option should be sent in format:

```xml
<selectedOperPayment>
    <id>cc</id>
    <for>MAIN</for>
    <fields>
        <card_type>VI</card_type>
        <card_owner>Jan Kowalski</card_owner>
        <card_number>4111111111111111</card_number>
        <card_valid_mm>05</card_valid_mm>
        <card_valid_yy>25</card_valid_yy>
        <card_cvv>123</card_cvv>
    </fields>
</selectedOperPayment>
```

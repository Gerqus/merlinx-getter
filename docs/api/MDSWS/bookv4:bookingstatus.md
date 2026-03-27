## POST /bookV4/bookingstatus

Request for getting detailed information about the booking.

### Request

Type:
| Element name | Description |
| --- | --- |
| bookingstatus | get booking status |

Conditions:
| Element name | Description |
| --- | --- |
| language | Language code |
| currency | Currency code |
| ofr_tourOp | Touroperator code |
| expedient_code | Expedient code |
| booking_number | number of a booking |

**For networks:** Main agency has access to it's network subagencies bookings. If authorization problems occur please contact MerlinX Support to verify that the agent is set up correctly, as a network. The query and response look identical to the standard query but with a different agency number provided.

<?xml version="1.0" encoding="UTF-8"?>
<mds>
  <auth>
    <login>login</login>
    <pass>password</pass>
    <source>B2C</source>
    <srcDomain>source.domain.pl</srcDomain>
    <consultant>TEST</consultant>
  </auth>
  <request>
    <conditions>
      <language>PL</language>
      <currency>PLN</currency>
      <ofr_tourOp>EXIM</ofr_tourOp>
      <expedient_code>EECD</expedient_code>
      <booking_number>632419232</booking_number>
    </conditions>
    <forminfo/>
    <type>bookingstatus</type>
  </request>
</mds>

### Answer

Possible codes for reservation status:

| Code | Description |
| --- | --- |
| XX  | Canceled booking |
| RQ  | Reservation “on request” |
| RR  | Booking on request CONFIRMED by Tour-operator |
| OP  | Option reservation |
| RF  | CONFIRMED option reservation |
| OK  | Firm booking |
| ??  | Because of technical reasons, booking status could not be determined |

Answer sections:

| Name | Description | Optional |
| --- | --- | --- |
| result | Basic booking information |     |
| agency_messages | Messages defined by agency | true |
| option | Optional booking information | true |
| pricetotal | Booking price information |     |
| catalog | Booking catalog information | true |
| prices | Booking commission information (element notProvided will appear if not given by touroperator) | true |
| hotel | Booking hotel information |     |
| transport | Booking transport information |     |
| services | Booking services |     |
| offers | Booking offer information | true |
| forminfo | Information regarding booking persons, client and other elements necessary for booking |     |
| payment | Information about payment details and schedule (possible 'type' values are BookingFee/PrePayment/RestPayment). Element notProvided will appear for requirements and touroperatorInfo sections if not given by touroperator |     |
| hints | Additional text information | true |

Forminfo section field definition attributes:

| Attribute | Description |
| --- | --- |
| type | Field type |
| maxlength | Maximum length of field value |
| name | Displayed field name in requested language |
| required | Defines whether the field is required or not for CONFIRMED booking |
| requiredOption | Defines whether the field is required or not for OPTIONAL booking |

Possible values for forminfo field type attribute:

| Type | Description |
| --- | --- |
| string | Alpha-numeric string |
| postcode | Post code |
| phone | Phone number |
| email | E-mail address |
| selected | Select field (should always return possible option list) |

<response>
    <result msgCode="205" bookingNumber="632419398" status="OP" msgText="Przedstawienie rezerwacji" orgOperText="" subCode=""/>
    <agency_messages/>
    <option possible="1" optiondate="2019-09-07" optiontime="13:29:00"/>
    <pricetotal operPrice="4158.00" operCurrency="PLN"/>
    <catalog/>
    <prices>
        <commissionGross>0.00</commissionGross>
        <currency>PLN</currency>
    </prices>
    <hotel code="26282" name="Zephir Hotel &amp; SPA" roomCode="720825" roomDesc="DBL Standard" mealCode="AI" mealDesc="All inclusive" city="" country="" category="" depDate="2020-05-14" desDate="2020-05-21" status="OP" price="" currency="" tourop="" bookingNr=""/>
    <transport type="F" carrierCode="CHR" carrierDesc="" depCode="KTW" desCode="DJE" depDate="2020-05-14" desDate="2020-05-14" rDepCode="DJE" rDesCode="KTW" rDepDate="2020-05-21" rDesDate="2020-05-21" depTime="00:00" desTime="00:00" rDepTime="00:00" rDesTime="00:00" flightNo="" rFlightNo="" flightCode="KTWDJE" rFlightCode="DJEKTW" status="OP" price="" currency="" handBaggage="" rHandBaggage="" regBaggage="" rRegBaggage=""/>
    <services>
        <service desc="Ubezpieczenie od Skutkow Ataku Terrorystycznego_OPTYMALNE - ERGO Ubezpieczenia Podrozy" perPassenger="1" perBooking="0" total_price="68.00" currency="PLN">
            <debug/>
            <code>UBZP</code>
            <type>INSURANCE</type>
            <excludedCodes/>
            <requiredCodes/>
            <checked>1</checked>
            <flightData/>
            <allocations>
                <allocate type="">
                    <person>1</person>
                    <checked>1</checked>
                    <options_list/>
                </allocate>
                <allocate type="">
                    <person>2</person>
                    <checked>1</checked>
                    <options_list/>
                </allocate>
            </allocations>
            <prices/>
            <hints/>
            <options/>
        </service>
        <service desc="Ubezpieczenie Optymalne - KL, NNW, bagazu, nastepstw chorob przewleklych - ERGO Ubezpieczenia Podrozy" perPassenger="1" perBooking="0" total_price="92.00" currency="PLN">
            <debug/>
            <code>UBZP</code>
            <type>INSURANCE</type>
            <excludedCodes/>
            <requiredCodes/>
            <checked>1</checked>
            <flightData/>
            <allocations>
                <allocate type="">
                    <person>1</person>
                    <checked>1</checked>
                    <options_list/>
                </allocate>
                <allocate type="">
                    <person>2</person>
                    <checked>1</checked>
                    <options_list/>
                </allocate>
            </allocations>
            <prices/>
            <hints/>
            <options/>
        </service>
        <service desc="Ubezpieczenie podstawowe- KL, NNW, bagazu, nastepstw chorob przewleklych - ERGO Ubezpieczenia Podrozy" perPassenger="1" perBooking="0" total_price="24.00" currency="PLN">
            <debug/>
            <code>UBZP</code>
            <type>INSURANCE</type>
            <excludedCodes/>
            <requiredCodes/>
            <checked>1</checked>
            <flightData/>
            <allocations>
                <allocate type="">
                    <person>1</person>
                    <checked>1</checked>
                    <options_list/>
                </allocate>
                <allocate type="">
                    <person>2</person>
                    <checked>1</checked>
                    <options_list/>
                </allocate>
            </allocations>
            <prices/>
            <hints/>
            <options/>
        </service>
        <service desc="Turystyczny Fundusz Gwarancyjny - 15 PLN/osoba" perPassenger="1" perBooking="0" total_price="15.00" currency="PLN">
            <debug/>
            <code>TFG</code>
            <type>TFG</type>
            <excludedCodes/>
            <requiredCodes/>
            <checked>1</checked>
            <flightData/>
            <allocations>
                <allocate type="">
                    <person>1</person>
                    <checked>1</checked>
                    <options_list/>
                </allocate>
                <allocate type="">
                    <person>2</person>
                    <checked>1</checked>
                    <options_list/>
                </allocate>
            </allocations>
            <prices/>
            <hints/>
            <options/>
        </service>
        <service desc="Transfer (airport/hotel/airport)" perPassenger="1" perBooking="0" total_price="0.00" currency="PLN">
            <debug/>
            <code>TRSF</code>
            <type>TRANSFER</type>
            <excludedCodes/>
            <requiredCodes/>
            <checked>1</checked>
            <flightData/>
            <allocations>
                <allocate type="">
                    <person>1</person>
                    <checked>1</checked>
                    <options_list/>
                </allocate>
                <allocate type="">
                    <person>2</person>
                    <checked>1</checked>
                    <options_list/>
                </allocate>
            </allocations>
            <prices/>
            <hints/>
            <options/>
        </service>
        <service desc="Gwarancja Niezmiennosci Ceny - Gratis" perPassenger="1" perBooking="0" total_price="0.00" currency="PLN">
            <debug/>
            <code>PROM</code>
            <type>S</type>
            <excludedCodes/>
            <requiredCodes/>
            <checked>1</checked>
            <flightData/>
            <allocations>
                <allocate type="">
                    <person>1</person>
                    <checked>1</checked>
                    <options_list/>
                </allocate>
                <allocate type="">
                    <person>2</person>
                    <checked>1</checked>
                    <options_list/>
                </allocate>
            </allocations>
            <prices/>
            <hints/>
            <options/>
        </service>
        <service desc="Ubezpieczenie nastepstw chorob przewleklych - GRATIS" perPassenger="1" perBooking="0" total_price="0.00" currency="PLN">
            <debug/>
            <code>PROM</code>
            <type>S</type>
            <excludedCodes/>
            <requiredCodes/>
            <checked>1</checked>
            <flightData/>
            <allocations>
                <allocate type="">
                    <person>1</person>
                    <checked>1</checked>
                    <options_list/>
                </allocate>
                <allocate type="">
                    <person>2</person>
                    <checked>1</checked>
                    <options_list/>
                </allocate>
            </allocations>
            <prices/>
            <hints/>
            <options/>
        </service>
    </services>
    <debug/>
    <offers/>
    <forminfo>
        <persons>
            <person price="2079.00" currency="">
                <person_id type="integer">1</person_id>
                <gender type="selected" maxlength="1" name="Płeć" required="1" requiredOption="1">
                    <option value="H" selected="1">Pan</option>
                    <option value="F">Pani</option>
                </gender>
                <lastname type="string" maxlength="30" name="Nazwisko" required="1" requiredOption="1">MERLIN-TEST</lastname>
                <firstname type="string" maxlength="30" name="Imię" required="1" requiredOption="1">TEST</firstname>
                <birthdate type="date" maxlength="10" name="Data urodzenia" required="1" requiredOption="1">05.09.1989</birthdate>
                <passport_number type="string" maxlength="20" name="Paszport" required="0" requiredOption="0"/>
                <post_code type="postcode" maxlength="6" name="Kod" required="1" requiredOption="1">12-345</post_code>
                <city type="string" maxlength="30" name="Miejscowość" required="1" requiredOption="1">WARSZAWA</city>
                <address type="string" maxlength="30" name="Ulica" required="1" requiredOption="1">TESTOWA</address>
                <country_code type="selected" maxlength="2" name="Kraj" required="1" requiredOption="1">
                    <option value="AF">Afganistan</option>
                    <option value="AL">Albania</option>
                    <option value="DZ">Algieria</option>
                    <option value="AD">Andora</option>
                    <option value="AO">Angola</option>
                    <option value="AI">Anguilla</option>
                    <option value="AG">Antigua i Barbuda</option>
                    <option value="SA">Arabia Saudyjska</option>
                    <option value="AR">Argentyna</option>
                    <option value="AM">Armenia</option>
                    <option value="AW">Aruba</option>
                    <option value="AU">Australia</option>
                    <option value="AT">Austria</option>
                    <option value="AZ">Azerbejdżan</option>
                    <option value="BS">Bahamy</option>
                    <option value="BH">Bahrajn</option>
                    <option value="BD">Bangladesz</option>
                    <option value="BB">Barbados</option>
                    <option value="BE">Belgia</option>
                    <option value="BZ">Belize</option>
                    <option value="BJ">Benin</option>
                    <option value="BM">Bermudy</option>
                    <option value="BT">Bhutan</option>
                    <option value="BY">Białoruś</option>
                    <option value="MM">Birma</option>
                    <option value="BO">Boliwia</option>
                    <option value="BQ">Bonaire</option>
                    <option value="BW">Botswana</option>
                    <option value="BA">Bośnia i Hercegowina</option>
                    <option value="BR">Brazylia</option>
                    <option value="BN">Brunei</option>
                    <option value="VG">Brytyjskie Wyspy Dziewicze</option>
                    <option value="BF">Burkina Faso</option>
                    <option value="BI">Burundi</option>
                    <option value="BG">Bułgaria</option>
                    <option value="CL">Chile</option>
                    <option value="HK">Chiny</option>
                    <option value="MO">Chiny</option>
                    <option value="CN">Chiny</option>
                    <option value="HR">Chorwacja</option>
                    <option value="CW">Curaçao</option>
                    <option value="CY">Cypr</option>
                    <option value="TD">Czad</option>
                    <option value="ME">Czarnogóra</option>
                    <option value="CZ">Czechy</option>
                    <option value="DK">Dania</option>
                    <option value="DM">Dominika</option>
                    <option value="DO">Dominikana</option>
                    <option value="DJ">Dżibuti</option>
                    <option value="EG">Egipt</option>
                    <option value="EC">Ekwador</option>
                    <option value="ER">Erytrea</option>
                    <option value="EE">Estonia</option>
                    <option value="ET">Etiopia</option>
                    <option value="FJ">Fidżi</option>
                    <option value="PH">Filipiny</option>
                    <option value="FI">Finlandia</option>
                    <option value="FR">Francja</option>
                    <option value="GA">Gabon</option>
                    <option value="GM">Gambia</option>
                    <option value="GH">Ghana</option>
                    <option value="GI">Gibraltar</option>
                    <option value="GR">Grecja</option>
                    <option value="GD">Grenada</option>
                    <option value="GL">Grenlandia</option>
                    <option value="GE">Gruzja</option>
                    <option value="GU">Guam</option>
                    <option value="GY">Gujana</option>
                    <option value="GF">Gujana Francuska</option>
                    <option value="GP">Gwadelupa</option>
                    <option value="GT">Gwatemala</option>
                    <option value="GN">Gwinea</option>
                    <option value="GW">Gwinea Bissau</option>
                    <option value="GQ">Gwinea Równikowa</option>
                    <option value="HT">Haiti</option>
                    <option value="ES">Hiszpania</option>
                    <option value="NL">Holandia</option>
                    <option value="HN">Honduras</option>
                    <option value="IN">Indie</option>
                    <option value="ID">Indonezja</option>
                    <option value="IQ">Irak</option>
                    <option value="IR">Iran</option>
                    <option value="IE">Irlandia</option>
                    <option value="IS">Islandia</option>
                    <option value="IL">Izrael</option>
                    <option value="JM">Jamajka</option>
                    <option value="JP">Japonia</option>
                    <option value="YE">Jemen</option>
                    <option value="JO">Jordania</option>
                    <option value="KY">Kajmany</option>
                    <option value="KH">Kambodża</option>
                    <option value="CM">Kamerun</option>
                    <option value="CA">Kanada</option>
                    <option value="QA">Katar</option>
                    <option value="KZ">Kazachstan</option>
                    <option value="KE">Kenia</option>
                    <option value="KG">Kirgistan</option>
                    <option value="KI">Kiribati</option>
                    <option value="CO">Kolumbia</option>
                    <option value="KM">Komory</option>
                    <option value="CG">Kongo</option>
                    <option value="KR">Korea Południowa</option>
                    <option value="KP">Korea Północna</option>
                    <option value="XK">Kosowo</option>
                    <option value="CR">Kostaryka</option>
                    <option value="MC">Księstwo Monako</option>
                    <option value="CU">Kuba</option>
                    <option value="KW">Kuwejt</option>
                    <option value="LA">Laos</option>
                    <option value="LS">Lesotho</option>
                    <option value="LB">Liban</option>
                    <option value="LR">Liberia</option>
                    <option value="LY">Libia</option>
                    <option value="LI">Liechtenstein</option>
                    <option value="LT">Litwa</option>
                    <option value="LU">Luksemburg</option>
                    <option value="MK">Macedonia</option>
                    <option value="MG">Madagaskar</option>
                    <option value="MW">Malawi</option>
                    <option value="MV">Malediwy</option>
                    <option value="MY">Malezja</option>
                    <option value="ML">Mali</option>
                    <option value="MT">Malta</option>
                    <option value="MP">Mariany Północne</option>
                    <option value="EH">Maroko</option>
                    <option value="MA">Maroko</option>
                    <option value="MQ">Martynika</option>
                    <option value="MR">Mauretania</option>
                    <option value="MU">Mauritius</option>
                    <option value="YT">Mayotte</option>
                    <option value="MX">Meksyk</option>
                    <option value="MN">Mongolia</option>
                    <option value="MZ">Mozambik</option>
                    <option value="MD">Mołdawia</option>
                    <option value="NA">Namibia</option>
                    <option value="NR">Nauru</option>
                    <option value="NP">Nepal</option>
                    <option value="DE">Niemcy</option>
                    <option value="NE">Niger</option>
                    <option value="NG">Nigeria</option>
                    <option value="NI">Nikaragua</option>
                    <option value="NU">Niue</option>
                    <option value="NO">Norwegia</option>
                    <option value="NC">Nowa Kaledonia</option>
                    <option value="NZ">Nowa Zelandia</option>
                    <option value="OM">Oman</option>
                    <option value="PK">Pakistan</option>
                    <option value="PW">Palau</option>
                    <option value="PA">Panama</option>
                    <option value="PG">Papua-Nowa Gwinea</option>
                    <option value="PY">Paragwaj</option>
                    <option value="PE">Peru</option>
                    <option value="PF">Polinezja Francuska</option>
                    <option value="PL">Polska</option>
                    <option value="PR">Portoryko</option>
                    <option value="PT">Portugalia</option>
                    <option value="ZA">Republika Południowej Afryki</option>
                    <option value="CV">Republika Zielonego Przylądka</option>
                    <option value="CF">Republika Środkowoafrykańska</option>
                    <option value="RE">Reunion</option>
                    <option value="RU">Rosja</option>
                    <option value="RO">Rumunia</option>
                    <option value="RW">Rwanda</option>
                    <option value="KN">Saint Kitts i Nevis</option>
                    <option value="LC">Saint Lucia</option>
                    <option value="VC">Saint Vincent i Grenadyny</option>
                    <option value="BL">Saint-Barthélemy</option>
                    <option value="MF">Saint-Martin</option>
                    <option value="PM">Saint-Pierre i Miquelon</option>
                    <option value="SV">Salwador</option>
                    <option value="WS">Samoa</option>
                    <option value="AS">Samoa Amerykańskie</option>
                    <option value="SM">San Marino</option>
                    <option value="SN">Senegal</option>
                    <option value="RS">Serbia</option>
                    <option value="SC">Seszele</option>
                    <option value="FM">Sfederowane Stany Mikronezji</option>
                    <option value="SL">Sierra Leone</option>
                    <option value="SG">Singapur</option>
                    <option value="SO">Somalia</option>
                    <option value="LK">Sri Lanka</option>
                    <option value="US">Stany Zjednoczone</option>
                    <option value="SZ">Suazi</option>
                    <option value="SD">Sudan</option>
                    <option value="SS">Sudan Południowy</option>
                    <option value="SR">Surinam</option>
                    <option value="SY">Syria</option>
                    <option value="CH">Szwajcaria</option>
                    <option value="SE">Szwecja</option>
                    <option value="SK">Słowacja</option>
                    <option value="SI">Słowenia</option>
                    <option value="TJ">Tadżykistan</option>
                    <option value="TH">Tajlandia</option>
                    <option value="TW">Tajwan</option>
                    <option value="TZ">Tanzania</option>
                    <option value="TL">Timor Wschodni</option>
                    <option value="TG">Togo</option>
                    <option value="TO">Tonga</option>
                    <option value="TT">Trynidad i Tobago</option>
                    <option value="TN">Tunezja</option>
                    <option value="TR">Turcja</option>
                    <option value="TM">Turkmenistan</option>
                    <option value="TC">Turks i Caicos</option>
                    <option value="TV">Tuvalu</option>
                    <option value="UG">Uganda</option>
                    <option value="UA">Ukraina</option>
                    <option value="UY">Urugwaj</option>
                    <option value="UZ">Uzbekistan</option>
                    <option value="VU">Vanuatu</option>
                    <option value="VE">Wenezuela</option>
                    <option value="FK">Wielka Brytania</option>
                    <option value="GB">Wielka Brytania</option>
                    <option value="JE">Wielka Brytania</option>
                    <option value="VN">Wietnam</option>
                    <option value="CI">Wybrzeże Kości Słoniowej</option>
                    <option value="CK">Wyspy Cooka</option>
                    <option value="VI">Wyspy Dziewicze Stanów Zjednoczonych</option>
                    <option value="MH">Wyspy Marshalla</option>
                    <option value="FO">Wyspy Owcze</option>
                    <option value="SB">Wyspy Salomona</option>
                    <option value="ST">Wyspy Świętego Tomasza i Książęca</option>
                    <option value="HU">Węgry</option>
                    <option value="IT">Włochy</option>
                    <option value="ZM">Zambia</option>
                    <option value="ZW">Zimbabwe</option>
                    <option value="AE">Zjednoczone Emiraty Arabskie</option>
                    <option value="LV">Łotwa</option>
                </country_code>
                <phone type="phone" maxlength="12" name="Telefon" required="0" requiredOption="0">1234545678</phone>
                <email_address type="email" maxlength="50" name="E-mail" required="0" requiredOption="0">TEST@TEST.PL</email_address>
            </person>
            <person price="2079.00" currency="">
                <person_id type="integer">2</person_id>
                <gender type="selected" maxlength="1" name="Płeć" required="1" requiredOption="1">
                    <option value="H">Pan</option>
                    <option value="F" selected="1">Pani</option>
                </gender>
                <lastname type="string" maxlength="30" name="Nazwisko" required="1" requiredOption="1">MERLIN-TEST</lastname>
                <firstname type="string" maxlength="30" name="Imię" required="1" requiredOption="1">TEST</firstname>
                <birthdate type="date" maxlength="10" name="Data urodzenia" required="1" requiredOption="1">05.09.1989</birthdate>
                <passport_number type="string" maxlength="20" name="Paszport" required="0" requiredOption="0"/>
                <post_code type="postcode" maxlength="6" name="Kod" required="1" requiredOption="1">12-345</post_code>
                <city type="string" maxlength="30" name="Miejscowość" required="1" requiredOption="1">WARSZAWA</city>
                <address type="string" maxlength="30" name="Ulica" required="1" requiredOption="1">TESTOWA</address>
                <country_code type="selected" maxlength="2" name="Kraj" required="1" requiredOption="1">
                    <option value="AF">Afganistan</option>
                    <option value="AL">Albania</option>
                    <option value="DZ">Algieria</option>
                    <option value="AD">Andora</option>
                    <option value="AO">Angola</option>
                    <option value="AI">Anguilla</option>
                    <option value="AG">Antigua i Barbuda</option>
                    <option value="SA">Arabia Saudyjska</option>
                    <option value="AR">Argentyna</option>
                    <option value="AM">Armenia</option>
                    <option value="AW">Aruba</option>
                    <option value="AU">Australia</option>
                    <option value="AT">Austria</option>
                    <option value="AZ">Azerbejdżan</option>
                    <option value="BS">Bahamy</option>
                    <option value="BH">Bahrajn</option>
                    <option value="BD">Bangladesz</option>
                    <option value="BB">Barbados</option>
                    <option value="BE">Belgia</option>
                    <option value="BZ">Belize</option>
                    <option value="BJ">Benin</option>
                    <option value="BM">Bermudy</option>
                    <option value="BT">Bhutan</option>
                    <option value="BY">Białoruś</option>
                    <option value="MM">Birma</option>
                    <option value="BO">Boliwia</option>
                    <option value="BQ">Bonaire</option>
                    <option value="BW">Botswana</option>
                    <option value="BA">Bośnia i Hercegowina</option>
                    <option value="BR">Brazylia</option>
                    <option value="BN">Brunei</option>
                    <option value="VG">Brytyjskie Wyspy Dziewicze</option>
                    <option value="BF">Burkina Faso</option>
                    <option value="BI">Burundi</option>
                    <option value="BG">Bułgaria</option>
                    <option value="CL">Chile</option>
                    <option value="HK">Chiny</option>
                    <option value="MO">Chiny</option>
                    <option value="CN">Chiny</option>
                    <option value="HR">Chorwacja</option>
                    <option value="CW">Curaçao</option>
                    <option value="CY">Cypr</option>
                    <option value="TD">Czad</option>
                    <option value="ME">Czarnogóra</option>
                    <option value="CZ">Czechy</option>
                    <option value="DK">Dania</option>
                    <option value="DM">Dominika</option>
                    <option value="DO">Dominikana</option>
                    <option value="DJ">Dżibuti</option>
                    <option value="EG">Egipt</option>
                    <option value="EC">Ekwador</option>
                    <option value="ER">Erytrea</option>
                    <option value="EE">Estonia</option>
                    <option value="ET">Etiopia</option>
                    <option value="FJ">Fidżi</option>
                    <option value="PH">Filipiny</option>
                    <option value="FI">Finlandia</option>
                    <option value="FR">Francja</option>
                    <option value="GA">Gabon</option>
                    <option value="GM">Gambia</option>
                    <option value="GH">Ghana</option>
                    <option value="GI">Gibraltar</option>
                    <option value="GR">Grecja</option>
                    <option value="GD">Grenada</option>
                    <option value="GL">Grenlandia</option>
                    <option value="GE">Gruzja</option>
                    <option value="GU">Guam</option>
                    <option value="GY">Gujana</option>
                    <option value="GF">Gujana Francuska</option>
                    <option value="GP">Gwadelupa</option>
                    <option value="GT">Gwatemala</option>
                    <option value="GN">Gwinea</option>
                    <option value="GW">Gwinea Bissau</option>
                    <option value="GQ">Gwinea Równikowa</option>
                    <option value="HT">Haiti</option>
                    <option value="ES">Hiszpania</option>
                    <option value="NL">Holandia</option>
                    <option value="HN">Honduras</option>
                    <option value="IN">Indie</option>
                    <option value="ID">Indonezja</option>
                    <option value="IQ">Irak</option>
                    <option value="IR">Iran</option>
                    <option value="IE">Irlandia</option>
                    <option value="IS">Islandia</option>
                    <option value="IL">Izrael</option>
                    <option value="JM">Jamajka</option>
                    <option value="JP">Japonia</option>
                    <option value="YE">Jemen</option>
                    <option value="JO">Jordania</option>
                    <option value="KY">Kajmany</option>
                    <option value="KH">Kambodża</option>
                    <option value="CM">Kamerun</option>
                    <option value="CA">Kanada</option>
                    <option value="QA">Katar</option>
                    <option value="KZ">Kazachstan</option>
                    <option value="KE">Kenia</option>
                    <option value="KG">Kirgistan</option>
                    <option value="KI">Kiribati</option>
                    <option value="CO">Kolumbia</option>
                    <option value="KM">Komory</option>
                    <option value="CG">Kongo</option>
                    <option value="KR">Korea Południowa</option>
                    <option value="KP">Korea Północna</option>
                    <option value="XK">Kosowo</option>
                    <option value="CR">Kostaryka</option>
                    <option value="MC">Księstwo Monako</option>
                    <option value="CU">Kuba</option>
                    <option value="KW">Kuwejt</option>
                    <option value="LA">Laos</option>
                    <option value="LS">Lesotho</option>
                    <option value="LB">Liban</option>
                    <option value="LR">Liberia</option>
                    <option value="LY">Libia</option>
                    <option value="LI">Liechtenstein</option>
                    <option value="LT">Litwa</option>
                    <option value="LU">Luksemburg</option>
                    <option value="MK">Macedonia</option>
                    <option value="MG">Madagaskar</option>
                    <option value="MW">Malawi</option>
                    <option value="MV">Malediwy</option>
                    <option value="MY">Malezja</option>
                    <option value="ML">Mali</option>
                    <option value="MT">Malta</option>
                    <option value="MP">Mariany Północne</option>
                    <option value="EH">Maroko</option>
                    <option value="MA">Maroko</option>
                    <option value="MQ">Martynika</option>
                    <option value="MR">Mauretania</option>
                    <option value="MU">Mauritius</option>
                    <option value="YT">Mayotte</option>
                    <option value="MX">Meksyk</option>
                    <option value="MN">Mongolia</option>
                    <option value="MZ">Mozambik</option>
                    <option value="MD">Mołdawia</option>
                    <option value="NA">Namibia</option>
                    <option value="NR">Nauru</option>
                    <option value="NP">Nepal</option>
                    <option value="DE">Niemcy</option>
                    <option value="NE">Niger</option>
                    <option value="NG">Nigeria</option>
                    <option value="NI">Nikaragua</option>
                    <option value="NU">Niue</option>
                    <option value="NO">Norwegia</option>
                    <option value="NC">Nowa Kaledonia</option>
                    <option value="NZ">Nowa Zelandia</option>
                    <option value="OM">Oman</option>
                    <option value="PK">Pakistan</option>
                    <option value="PW">Palau</option>
                    <option value="PA">Panama</option>
                    <option value="PG">Papua-Nowa Gwinea</option>
                    <option value="PY">Paragwaj</option>
                    <option value="PE">Peru</option>
                    <option value="PF">Polinezja Francuska</option>
                    <option value="PL">Polska</option>
                    <option value="PR">Portoryko</option>
                    <option value="PT">Portugalia</option>
                    <option value="ZA">Republika Południowej Afryki</option>
                    <option value="CV">Republika Zielonego Przylądka</option>
                    <option value="CF">Republika Środkowoafrykańska</option>
                    <option value="RE">Reunion</option>
                    <option value="RU">Rosja</option>
                    <option value="RO">Rumunia</option>
                    <option value="RW">Rwanda</option>
                    <option value="KN">Saint Kitts i Nevis</option>
                    <option value="LC">Saint Lucia</option>
                    <option value="VC">Saint Vincent i Grenadyny</option>
                    <option value="BL">Saint-Barthélemy</option>
                    <option value="MF">Saint-Martin</option>
                    <option value="PM">Saint-Pierre i Miquelon</option>
                    <option value="SV">Salwador</option>
                    <option value="WS">Samoa</option>
                    <option value="AS">Samoa Amerykańskie</option>
                    <option value="SM">San Marino</option>
                    <option value="SN">Senegal</option>
                    <option value="RS">Serbia</option>
                    <option value="SC">Seszele</option>
                    <option value="FM">Sfederowane Stany Mikronezji</option>
                    <option value="SL">Sierra Leone</option>
                    <option value="SG">Singapur</option>
                    <option value="SO">Somalia</option>
                    <option value="LK">Sri Lanka</option>
                    <option value="US">Stany Zjednoczone</option>
                    <option value="SZ">Suazi</option>
                    <option value="SD">Sudan</option>
                    <option value="SS">Sudan Południowy</option>
                    <option value="SR">Surinam</option>
                    <option value="SY">Syria</option>
                    <option value="CH">Szwajcaria</option>
                    <option value="SE">Szwecja</option>
                    <option value="SK">Słowacja</option>
                    <option value="SI">Słowenia</option>
                    <option value="TJ">Tadżykistan</option>
                    <option value="TH">Tajlandia</option>
                    <option value="TW">Tajwan</option>
                    <option value="TZ">Tanzania</option>
                    <option value="TL">Timor Wschodni</option>
                    <option value="TG">Togo</option>
                    <option value="TO">Tonga</option>
                    <option value="TT">Trynidad i Tobago</option>
                    <option value="TN">Tunezja</option>
                    <option value="TR">Turcja</option>
                    <option value="TM">Turkmenistan</option>
                    <option value="TC">Turks i Caicos</option>
                    <option value="TV">Tuvalu</option>
                    <option value="UG">Uganda</option>
                    <option value="UA">Ukraina</option>
                    <option value="UY">Urugwaj</option>
                    <option value="UZ">Uzbekistan</option>
                    <option value="VU">Vanuatu</option>
                    <option value="VE">Wenezuela</option>
                    <option value="FK">Wielka Brytania</option>
                    <option value="GB">Wielka Brytania</option>
                    <option value="JE">Wielka Brytania</option>
                    <option value="VN">Wietnam</option>
                    <option value="CI">Wybrzeże Kości Słoniowej</option>
                    <option value="CK">Wyspy Cooka</option>
                    <option value="VI">Wyspy Dziewicze Stanów Zjednoczonych</option>
                    <option value="MH">Wyspy Marshalla</option>
                    <option value="FO">Wyspy Owcze</option>
                    <option value="SB">Wyspy Salomona</option>
                    <option value="ST">Wyspy Świętego Tomasza i Książęca</option>
                    <option value="HU">Węgry</option>
                    <option value="IT">Włochy</option>
                    <option value="ZM">Zambia</option>
                    <option value="ZW">Zimbabwe</option>
                    <option value="AE">Zjednoczone Emiraty Arabskie</option>
                    <option value="LV">Łotwa</option>
                </country_code>
                <phone type="phone" maxlength="12" name="Telefon" required="0" requiredOption="0">1234545678</phone>
                <email_address type="email" maxlength="50" name="E-mail" required="0" requiredOption="0"/>
            </person>
        </persons>
        <client>
            <title type="selected" maxlength="1" name="Płeć" required="0" requiredOption="0">
                <option value="H">Pan</option>
                <option value="F">Pani</option>
            </title>
            <lastname type="string" maxlength="30" name="Nazwisko" required="1" requiredOption="1">MERLIN-TEST</lastname>
            <firstname type="string" maxlength="30" name="Imię" required="1" requiredOption="1">TEST</firstname>
            <address type="string" maxlength="30" name="Ulica" required="1" requiredOption="1">TESTOWA</address>
            <post_code type="postcode" maxlength="6" name="Kod" required="1" requiredOption="1">12-345</post_code>
            <city type="string" maxlength="30" name="Miejscowość" required="1" requiredOption="1">WARSZAWA</city>
            <country type="selected" maxlength="2" name="Kraj" required="0" requiredOption="0">
                <option value="AF">Afganistan</option>
                <option value="AL">Albania</option>
                <option value="DZ">Algieria</option>
                <option value="AD">Andora</option>
                <option value="AO">Angola</option>
                <option value="AI">Anguilla</option>
                <option value="AG">Antigua i Barbuda</option>
                <option value="SA">Arabia Saudyjska</option>
                <option value="AR">Argentyna</option>
                <option value="AM">Armenia</option>
                <option value="AW">Aruba</option>
                <option value="AU">Australia</option>
                <option value="AT">Austria</option>
                <option value="AZ">Azerbejdżan</option>
                <option value="BS">Bahamy</option>
                <option value="BH">Bahrajn</option>
                <option value="BD">Bangladesz</option>
                <option value="BB">Barbados</option>
                <option value="BE">Belgia</option>
                <option value="BZ">Belize</option>
                <option value="BJ">Benin</option>
                <option value="BM">Bermudy</option>
                <option value="BT">Bhutan</option>
                <option value="BY">Białoruś</option>
                <option value="MM">Birma</option>
                <option value="BO">Boliwia</option>
                <option value="BQ">Bonaire</option>
                <option value="BW">Botswana</option>
                <option value="BA">Bośnia i Hercegowina</option>
                <option value="BR">Brazylia</option>
                <option value="BN">Brunei</option>
                <option value="VG">Brytyjskie Wyspy Dziewicze</option>
                <option value="BF">Burkina Faso</option>
                <option value="BI">Burundi</option>
                <option value="BG">Bułgaria</option>
                <option value="CL">Chile</option>
                <option value="HK">Chiny</option>
                <option value="MO">Chiny</option>
                <option value="CN">Chiny</option>
                <option value="HR">Chorwacja</option>
                <option value="CW">Curaçao</option>
                <option value="CY">Cypr</option>
                <option value="TD">Czad</option>
                <option value="ME">Czarnogóra</option>
                <option value="CZ">Czechy</option>
                <option value="DK">Dania</option>
                <option value="DM">Dominika</option>
                <option value="DO">Dominikana</option>
                <option value="DJ">Dżibuti</option>
                <option value="EG">Egipt</option>
                <option value="EC">Ekwador</option>
                <option value="ER">Erytrea</option>
                <option value="EE">Estonia</option>
                <option value="ET">Etiopia</option>
                <option value="FJ">Fidżi</option>
                <option value="PH">Filipiny</option>
                <option value="FI">Finlandia</option>
                <option value="FR">Francja</option>
                <option value="GA">Gabon</option>
                <option value="GM">Gambia</option>
                <option value="GH">Ghana</option>
                <option value="GI">Gibraltar</option>
                <option value="GR">Grecja</option>
                <option value="GD">Grenada</option>
                <option value="GL">Grenlandia</option>
                <option value="GE">Gruzja</option>
                <option value="GU">Guam</option>
                <option value="GY">Gujana</option>
                <option value="GF">Gujana Francuska</option>
                <option value="GP">Gwadelupa</option>
                <option value="GT">Gwatemala</option>
                <option value="GN">Gwinea</option>
                <option value="GW">Gwinea Bissau</option>
                <option value="GQ">Gwinea Równikowa</option>
                <option value="HT">Haiti</option>
                <option value="ES">Hiszpania</option>
                <option value="NL">Holandia</option>
                <option value="HN">Honduras</option>
                <option value="IN">Indie</option>
                <option value="ID">Indonezja</option>
                <option value="IQ">Irak</option>
                <option value="IR">Iran</option>
                <option value="IE">Irlandia</option>
                <option value="IS">Islandia</option>
                <option value="IL">Izrael</option>
                <option value="JM">Jamajka</option>
                <option value="JP">Japonia</option>
                <option value="YE">Jemen</option>
                <option value="JO">Jordania</option>
                <option value="KY">Kajmany</option>
                <option value="KH">Kambodża</option>
                <option value="CM">Kamerun</option>
                <option value="CA">Kanada</option>
                <option value="QA">Katar</option>
                <option value="KZ">Kazachstan</option>
                <option value="KE">Kenia</option>
                <option value="KG">Kirgistan</option>
                <option value="KI">Kiribati</option>
                <option value="CO">Kolumbia</option>
                <option value="KM">Komory</option>
                <option value="CG">Kongo</option>
                <option value="KR">Korea Południowa</option>
                <option value="KP">Korea Północna</option>
                <option value="XK">Kosowo</option>
                <option value="CR">Kostaryka</option>
                <option value="MC">Księstwo Monako</option>
                <option value="CU">Kuba</option>
                <option value="KW">Kuwejt</option>
                <option value="LA">Laos</option>
                <option value="LS">Lesotho</option>
                <option value="LB">Liban</option>
                <option value="LR">Liberia</option>
                <option value="LY">Libia</option>
                <option value="LI">Liechtenstein</option>
                <option value="LT">Litwa</option>
                <option value="LU">Luksemburg</option>
                <option value="MK">Macedonia</option>
                <option value="MG">Madagaskar</option>
                <option value="MW">Malawi</option>
                <option value="MV">Malediwy</option>
                <option value="MY">Malezja</option>
                <option value="ML">Mali</option>
                <option value="MT">Malta</option>
                <option value="MP">Mariany Północne</option>
                <option value="EH">Maroko</option>
                <option value="MA">Maroko</option>
                <option value="MQ">Martynika</option>
                <option value="MR">Mauretania</option>
                <option value="MU">Mauritius</option>
                <option value="YT">Mayotte</option>
                <option value="MX">Meksyk</option>
                <option value="MN">Mongolia</option>
                <option value="MZ">Mozambik</option>
                <option value="MD">Mołdawia</option>
                <option value="NA">Namibia</option>
                <option value="NR">Nauru</option>
                <option value="NP">Nepal</option>
                <option value="DE">Niemcy</option>
                <option value="NE">Niger</option>
                <option value="NG">Nigeria</option>
                <option value="NI">Nikaragua</option>
                <option value="NU">Niue</option>
                <option value="NO">Norwegia</option>
                <option value="NC">Nowa Kaledonia</option>
                <option value="NZ">Nowa Zelandia</option>
                <option value="OM">Oman</option>
                <option value="PK">Pakistan</option>
                <option value="PW">Palau</option>
                <option value="PA">Panama</option>
                <option value="PG">Papua-Nowa Gwinea</option>
                <option value="PY">Paragwaj</option>
                <option value="PE">Peru</option>
                <option value="PF">Polinezja Francuska</option>
                <option value="PL">Polska</option>
                <option value="PR">Portoryko</option>
                <option value="PT">Portugalia</option>
                <option value="ZA">Republika Południowej Afryki</option>
                <option value="CV">Republika Zielonego Przylądka</option>
                <option value="CF">Republika Środkowoafrykańska</option>
                <option value="RE">Reunion</option>
                <option value="RU">Rosja</option>
                <option value="RO">Rumunia</option>
                <option value="RW">Rwanda</option>
                <option value="KN">Saint Kitts i Nevis</option>
                <option value="LC">Saint Lucia</option>
                <option value="VC">Saint Vincent i Grenadyny</option>
                <option value="BL">Saint-Barthélemy</option>
                <option value="MF">Saint-Martin</option>
                <option value="PM">Saint-Pierre i Miquelon</option>
                <option value="SV">Salwador</option>
                <option value="WS">Samoa</option>
                <option value="AS">Samoa Amerykańskie</option>
                <option value="SM">San Marino</option>
                <option value="SN">Senegal</option>
                <option value="RS">Serbia</option>
                <option value="SC">Seszele</option>
                <option value="FM">Sfederowane Stany Mikronezji</option>
                <option value="SL">Sierra Leone</option>
                <option value="SG">Singapur</option>
                <option value="SO">Somalia</option>
                <option value="LK">Sri Lanka</option>
                <option value="US">Stany Zjednoczone</option>
                <option value="SZ">Suazi</option>
                <option value="SD">Sudan</option>
                <option value="SS">Sudan Południowy</option>
                <option value="SR">Surinam</option>
                <option value="SY">Syria</option>
                <option value="CH">Szwajcaria</option>
                <option value="SE">Szwecja</option>
                <option value="SK">Słowacja</option>
                <option value="SI">Słowenia</option>
                <option value="TJ">Tadżykistan</option>
                <option value="TH">Tajlandia</option>
                <option value="TW">Tajwan</option>
                <option value="TZ">Tanzania</option>
                <option value="TL">Timor Wschodni</option>
                <option value="TG">Togo</option>
                <option value="TO">Tonga</option>
                <option value="TT">Trynidad i Tobago</option>
                <option value="TN">Tunezja</option>
                <option value="TR">Turcja</option>
                <option value="TM">Turkmenistan</option>
                <option value="TC">Turks i Caicos</option>
                <option value="TV">Tuvalu</option>
                <option value="UG">Uganda</option>
                <option value="UA">Ukraina</option>
                <option value="UY">Urugwaj</option>
                <option value="UZ">Uzbekistan</option>
                <option value="VU">Vanuatu</option>
                <option value="VE">Wenezuela</option>
                <option value="FK">Wielka Brytania</option>
                <option value="GB">Wielka Brytania</option>
                <option value="JE">Wielka Brytania</option>
                <option value="VN">Wietnam</option>
                <option value="CI">Wybrzeże Kości Słoniowej</option>
                <option value="CK">Wyspy Cooka</option>
                <option value="VI">Wyspy Dziewicze Stanów Zjednoczonych</option>
                <option value="MH">Wyspy Marshalla</option>
                <option value="FO">Wyspy Owcze</option>
                <option value="SB">Wyspy Salomona</option>
                <option value="ST">Wyspy Świętego Tomasza i Książęca</option>
                <option value="HU">Węgry</option>
                <option value="IT">Włochy</option>
                <option value="ZM">Zambia</option>
                <option value="ZW">Zimbabwe</option>
                <option value="AE">Zjednoczone Emiraty Arabskie</option>
                <option value="LV">Łotwa</option>
            </country>
            <phone type="phone" maxlength="12" name="Telefon" required="1" requiredOption="1">1234545678</phone>
            <email_address type="email" maxlength="50" name="E-mail" required="1" requiredOption="1">TEST@TEST.PL</email_address>
            <birthdate type="date" maxlength="10" name="Data urodzenia" required="0" requiredOption="0">05.09.1989</birthdate>
        </client>
        <services_optional groupsInclusive="0"/>
        <wishes/>
        <formalAgreements>
            <formalAgreement required="1">
                <code>uwagi_1</code>
                <selected>0</selected>
                <desc>Oświadczam, że przed zawarciem Umowy zostały przekazane Płatnikowi rezerwacji standardowe informacje Ustawy z dnia 24 listopada 2017.</desc>
            </formalAgreement>
        </formalAgreements>
        <paymentTable/>
        <externalServiceTable/>
    </forminfo>
    <payment>
        <requirements>
            <requirement>
                <type>PrePayment</type>
                <amount>198.00</amount>
                <paidAmount>0</paidAmount>
                <requiredTill>08.09.2019</requiredTill>
                <additionalInfo/>
            </requirement>
            <requirement>
                <type>RestPayment</type>
                <amount>3960.00</amount>
                <paidAmount>0</paidAmount>
                <requiredTill>23.04.2020</requiredTill>
                <additionalInfo/>
            </requirement>
        </requirements>
        <touroperatorInfo>
            <bankAccount>84103019996640000632419398</bankAccount>
            <bankName/>
            <name/>
            <address/>
            <streetNo/>
            <streetPlaceNo/>
            <postCode/>
            <city/>
            <emailAddress/>
            <phoneNo/>
            <faxNo/>
        </touroperatorInfo>
    </payment>
</response>

### Answer (with notProvided sections)

<response>
    <result msgCode="205" bookingNumber="1270749" status="OP" msgText="Przedstawienie rezerwacji" orgOperText="" subCode=""/>
    <agency_messages></agency_messages>
    <option possible="1" optiondate="06.09.19" optiontime="18:00"/>
    <pricetotal operPrice="1725.37" operCurrency="PLN"/>
    <catalog></catalog>
    <prices>
        <notProvided>true</notProvided>
    </prices>
    <hotel code="4077" name="BEIRUT HOTEL" roomCode="" roomDesc="room standard" mealCode="" mealDesc="half board" city="Hurghada" country="Egipt" category="30" depDate="19.03.2020" desDate="22.03.2020" 
           status="OP" price="" currency="" tourop="" bookingNr=""/>
    <transport type="F" carrierCode="" carrierDesc="" depCode="WAW" desCode="HRG" depDate="2020-03-19" desDate="2020-03-19" rDepCode="HRG" rDesCode="WAW" rDepDate="2020-03-22" rDesDate="2020-03-22"
               depTime="00:00" desTime="00:00" rDepTime="00:00" rDesTime="00:00" flightNo="ENT 4001" rFlightNo="ENT 4706" flightCode="WAWHRG" rFlightCode="HRGWAW" status="OP" price="" currency=""
               handBaggage="" rHandBaggage="" regBaggage="" rRegBaggage="">
        <flightOut>
            <flight depCode="WAW" depName="" desCode="HRG" desName="" depDate="2020-03-19" depTime="00:00" desDate="2020-03-19" desTime="00:00" flightNo="ENT 4001"/>
        </flightOut>
        <flightRet>
            <flight depCode="HRG" depName="" desCode="WAW" desName="" depDate="2020-03-22" depTime="00:00" desDate="2020-03-22" desTime="00:00" flightNo="ENT 4706"/>
        </flightRet>
    </transport>
    <services>
        <service desc="Group Transfer:Group 19.03.2020-22.03.2020:  HRG (Hurghada) --&gt; BEIRUT HOTEL (Hurghada) --&gt; HRG (Hurghada)" currency="PLN">
            <debug></debug>
            <code>Transfer</code>
            <type>S</type>
            <excludedCodes></excludedCodes>
            <requiredCodes></requiredCodes>
            <checked>1</checked>
            <flightData></flightData>
            <allocations>
                <allocate type="">
                    <checked>1</checked>
                    <options_list></options_list>
                </allocate>
            </allocations>
            <prices></prices>
            <hints></hints>
            <options></options>
        </service>
        <service desc="Składka do TFG  [19.03.2020]" currency="PLN">
            <debug></debug>
            <code>ExtraService</code>
            <type>S</type>
            <excludedCodes></excludedCodes>
            <requiredCodes></requiredCodes>
            <checked>1</checked>
            <flightData></flightData>
            <allocations>
                <allocate type="">
                    <checked>1</checked>
                    <options_list></options_list>
                </allocate>
            </allocations>
            <prices></prices>
            <hints></hints>
            <options></options>
        </service>
        <service desc="TRAVEL - Podstawowe - [19.03.2020 - 22.03.2020]" currency="PLN">
            <debug></debug>
            <code>Insurance</code>
            <type>V</type>
            <excludedCodes></excludedCodes>
            <requiredCodes></requiredCodes>
            <checked>1</checked>
            <flightData></flightData>
            <allocations>
                <allocate type="">
                    <checked>1</checked>
                    <options_list></options_list>
                </allocate>
            </allocations>
            <prices></prices>
            <hints></hints>
            <options></options>
        </service>
    </services>
    <debug></debug>
    <offers></offers>
    <forminfo>
        <persons>
            <person price="0" currency="">
                <person_id type="integer">1</person_id>
                <gender type="selected" maxlength="1" name="Płeć" required="1" requiredOption="1">
                    <option value="H">Pan</option>
                    <option value="F" selected="1">Pani</option>
                </gender>
                <lastname type="string" maxlength="30" name="Nazwisko" required="1" requiredOption="1">MERLIN</lastname>
                <firstname type="string" maxlength="30" name="Imię" required="1" requiredOption="1">JOJO</firstname>
                <birthdate type="date" maxlength="10" name="Data urodzenia" required="1" requiredOption="1">04.09.1989</birthdate>
            </person>
        </persons>
        <client>
            <title type="selected" maxlength="1" name="Płeć" required="1" requiredOption="1">
                <option value="H">Pan</option>
                <option value="F">Pani</option>
            </title>
            <lastname type="string" maxlength="30" name="Nazwisko" required="1" requiredOption="1">MERLIN</lastname>
            <firstname type="string" maxlength="30" name="Imię" required="1" requiredOption="1">JOJO</firstname>
            <address type="string" maxlength="30" name="Ulica" required="1" requiredOption="1">TESTOWE 23</address>
            <post_code type="postcode" maxlength="6" name="Kod" required="1" requiredOption="1">53-030</post_code>
            <city type="string" maxlength="30" name="Miejscowość" required="1" requiredOption="1">WROCLAW</city>
            <phone type="phone" maxlength="12" name="Telefon" required="1" requiredOption="1">717859270</phone>
            <email_address type="email" maxlength="50" name="E-mail" required="1" requiredOption="1">
                MERLINSUPPORT@E-SYSTEMY.COM
            </email_address>
            <birthdate type="date" maxlength="10" name="Data urodzenia" required="1" requiredOption="1"></birthdate>
        </client>
        <services_optional groupsInclusive="0">
            <service desc="TRAVEL - Podstawowe - [19.03.2020 - 22.03.2020]" currency="PLN">
                <debug></debug>
                <code>Insurance</code>
                <type>V</type>
                <excludedCodes></excludedCodes>
                <requiredCodes></requiredCodes>
                <checked>1</checked>
                <flightData></flightData>
                <allocations>
                    <allocate type="">
                        <checked>1</checked>
                        <options_list></options_list>
                    </allocate>
                </allocations>
                <prices></prices>
                <hints></hints>
                <options></options>
            </service>
        </services_optional>
        <wishes></wishes>
        <formalAgreements></formalAgreements>
        <paymentTable></paymentTable>
        <externalServiceTable></externalServiceTable>
    </forminfo>
    <payment>
        <requirements>
            <notProvided>true</notProvided>
        </requirements>
        <touroperatorInfo>
            <notProvided>true</notProvided>
        </touroperatorInfo>
    </payment>
</response>

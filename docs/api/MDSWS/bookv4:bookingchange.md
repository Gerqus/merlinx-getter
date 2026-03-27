## POST /bookv4/bookingchange

### Request

Type:
| Element name | Description |
| --- | --- |
| bookingchange | change a booking |

Conditions:
| Element name | Description |
| --- | --- |
| ofr_tourOp | offer tourop |
| booking_number | number of booking |
| language | language |

Forminfo:
| Element name | Description |
| --- | --- |
| person | info about persons |
| client | info about client |
| services_optional | additional services |

<?xml version="1.0" encoding="UTF-8"?>
<mds>
  <auth>
    <login>login</login>
    <pass>password</pass>
    <source>MDSWS</source>
    <srcDomain>test.mydomain.pl</srcDomain>
  </auth>
  <request>
    <type>bookingchange</type>
    <conditions>
      <language>PL</language>
      <ofr_tourOp>VITX</ofr_tourOp>
      <booking_number>1010060</booking_number>
    </conditions>
    <forminfo>
      <persons>
        <person int="0">
          <gender>H</gender>
          <lastname>TEST</lastname>
          <firstname>TEST</firstname>
          <birthdate>17.06.1986</birthdate>
          <address>TEST1</address>
          <post_code>12-345</post_code>
          <city>TEST1</city>
          <passport_number>45435</passport_number>
        </person>
      </persons>
      <client>
        <type>person</type>
        <gender>H</gender>
        <firstname>TEST</firstname>
        <lastname>TEST</lastname>
        <post_code>12-345</post_code>
        <post_city>TEST</post_city>
        <city>TEST</city>
        <address>TEST</address>
        <phone>5353</phone>
        <email_address>TEST@TEST.PL</email_address>
        <taxidentifier/>
        <country/>
        <birthdate>17.06.1986</birthdate>
      </client>
      <services_optional>
        <service int="0">
          <codeElemType>M</codeElemType>
          <codeOptServType>A</codeOptServType>
          <c ode>UBSCNL</co de>
          <type>INSURANCE_CNL</type>
          <checked>0</checked>
          <allocations>
            <allocate int="0">
              <value>1</value>
              <checked>0</checked>
            </allocate>
          </allocations>
          <date_from>14.04.2017</date_from>
          <date_to>17.04.2017</date_to>
        </service>
        <service int="1">
          <codeElemType>M</codeElemType>
          <codeOptServType>A</codeOptServType>
          <co de>UBSCNLP</co de>
          <type>INSURANCE_CNL</type>
          <checked>0</checked>
          <allocations>
            <allocate int="0">
              <value>1</value>
              <checked>0</checked>
            </allocate>
          </allocations>
          <date_from>14.04.2017</date_from>
          <date_to>17.04.2017</date_to>
        </service>
        <service int="2">
          <codeElemType>M</codeElemType>
          <codeOptServType>A</codeOptServType>
          <co de>UBSCAN</co de>
          <type>INSURANCE_CNL</type>
          <checked>0</checked>
          <allocations>
            <allocate int="0">
              <value>1</value>
              <checked>0</checked>
            </allocate>
          </allocations>
          <date_from>14.04.2017</date_from>
          <date_to>17.04.2017</date_to>
        </service>
        <service int="3">
          <codeElemType>M</codeElemType>
          <codeOptServType>A</codeOptServType>
          <co de>UBSCANP</cod e>
          <type>INSURANCE_CNL</type>
          <checked>0</checked>
          <allocations>
            <allocate int="0">
              <value>1</value>
              <checked>0</checked>
            </allocate>
          </allocations>
          <date_from>14.04.2017</date_from>
          <date_to>17.04.2017</date_to>
        </service>
        <service int="4">
          <codeElemType>A</codeElemType>
          <codeOptServType>R</codeOptServType>
          <co de>UBSCOMP</co de>
          <type>INSURANCE</type>
          <checked>0</checked>
          <allocations>
            <allocate int="0">
              <value>1</value>
              <checked>0</checked>
            </allocate>
          </allocations>
          <date_from>14.04.2017</date_from>
          <date_to>17.04.2017</date_to>
        </service>
        <service int="5">
          <codeElemType>A</codeElemType>
          <codeOptServType>R</codeOptServType>
          <co de>UBSCOPL</cod e>
          <type>INSURANCE</type>
          <checked>0</checked>
          <allocations>
            <allocate int="0">
              <value>1</value>
              <checked>0</checked>
            </allocate>
          </allocations>
          <date_from>14.04.2017</date_from>
          <date_to>17.04.2017</date_to>
        </service>
      </services_optional>
    </forminfo>
  </request>
</mds>

### Answer

<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<response>
  <result msgCode="203" bookingNumber="1010060" status="OP" msgText="Zmiana rezerwacji ok" orgOperText="Zmiana rezerwacji ok / Rezerwacja zostala zapisana bez bledow" subCode="999000290"/>
  <option possible="1" optiondate="2016-06-20" optiontime="13:00:00"/>
  <pricetotal operPrice="1509" operCurrency="PLN"/>
  <catalog>KPZ3</catalog>
  <prices>
    <commissionGross>105.90</commissionGross>
    <commissionNet>86.10</commissionNet>
    <margin>0.00</margin>
    <currency>PLN</currency>
  </prices>
  <hotel code="PARCITR" name="Paryz" roomCode="SRD" roomDesc="Dokwaterowanie w pok. 2/3 os." mealCode="F" mealDesc="sniadania" city="" country="Francja" category="" depDate="2017-04-14" desDate="2017-04-17" status="OK" price="1059" currency="PLN" tourop="" bookingNr=""/>
  <transport type="F" carrierCode="" carrierDesc="" depCode="WAW" desCode="PAR" depDate="2017-04-14" desDate="2017-04-14" rDepCode="PAR" rDesCode="WAW" rDepDate="2017-04-17" rDesDate="2017-04-17" depTime="00:00" desTime="00:00" rDepTime="00:00" rDesTime="00:00" flightNo="" rFlightNo="" status="OK" price="0" currency="PLN">
    <flightOut>
      <flight depCode="WAW" depName="Warszawa" desCode="PAR" desName="Paris" depDate="2017-04-14" depTime="00:00" desDate="2017-04-14" desTime="00:00" flightNo=""/>
    </flightOut>
    <flightRet>
      <flight depCode="PAR" depName="Paris" desCode="WAW" desName="Warszawa" depDate="2017-04-17" depTime="00:00" desDate="2017-04-17" desTime="00:00" flightNo=""/>
    </flightRet>
  </transport>
  <services>
    <service desc="Bilety wstepu, lokalni przewodnicy itp. platne dodatkowo na miejscu. Orientacyjne koszty podane w opisie imprezy oraz na www.itaka.pl" dateFromMin="2017-04-14T00:00:00" dateFromMax="2017-04-14T00:00:00" dateToMin="2017-04-17T00:00:00" dateToMax="2017-04-17T00:00:00" duration="3" status="OK" person_price="0" total_price="0" currency="PLN">
      <debug orgPrice="" priceFromBa="" priceFromBawithMargin="" priceFromBawithoutMargin="" decodeString="" orgOper="" currConvert=""/>
      <id>3</id>
      < cod e>TEXT_BILET</co de>
      <type>Usługa automatyczna</type>
      <codeElemType>A</codeElemType>
      <codeOptServType>X</codeOptServType>
      <packageType>NotSet</packageType>
      <date_from>14.04.2017</date_from>
      <date_to>17.04.2017</date_to>
      <checked>1</checked>
      <allocations>
        <allocate type="checkbox">
          <value>1</value>
          <checked>1</checked>
          <options_list/>
        </allocate>
      </allocations>
      <prices/>
      <hints/>
      <options/>
    </service>
    <service desc="Obowiazkowa oplata transportowa" dateFromMin="2017-04-14T00:00:00" dateFromMax="2017-04-14T00:00:00" dateToMin="2017-04-17T00:00:00" dateToMax="2017-04-17T00:00:00" duration="3" status="OK" person_price="150" total_price="150" currency="PLN">
      <debug orgPrice="" priceFromBa="" priceFromBawithMargin="" priceFromBawithoutMargin="" decodeString="" orgOper="" currConvert=""/>
      <id>4</id>
      <co de>PAR_DP_TRA</co de>
      <type>Transfer</type>
      <codeElemType>A</codeElemType>
      <codeOptServType>X</codeOptServType>
      <packageType>NotSet</packageType>
      <date_from>14.04.2017</date_from>
      <date_to>17.04.2017</date_to>
      <checked>1</checked>
      <allocations>
        <allocate type="checkbox">
          <value>1</value>
          <checked>1</checked>
          <options_list/>
        </allocate>
      </allocations>
      <prices/>
      <hints/>
      <options/>
    </service>
    <service desc="Ubezpieczenie Itaka Simple. Obejmuje koszty leczenia do 15 000 EUR" dateFromMin="2017-04-14T00:00:00" dateFromMax="2017-04-14T00:00:00" dateToMin="2017-04-17T00:00:00" dateToMax="2017-04-17T00:00:00" duration="3" status="OK" person_price="12" total_price="12" currency="PLN">
      <debug orgPrice="" priceFromBa="" priceFromBawithMargin="" priceFromBawithoutMargin="" decodeString="" orgOper="" currConvert=""/>
      <id>5</id>
      <c ode>UBSKLNW</c ode>
      <type>INSURANCE</type>
      <codeElemType>A</codeElemType>
      <codeOptServType>X</codeOptServType>
      <packageType>NotSet</packageType>
      <date_from>14.04.2017</date_from>
      <date_to>17.04.2017</date_to>
      <checked>1</checked>
      <allocations>
        <allocate type="checkbox">
          <value>1</value>
          <checked>1</checked>
          <options_list/>
        </allocate>
      </allocations>
      <prices/>
      <hints/>
      <options/>
    </service>
    <service desc="Ubezpieczenie wliczone w cene" dateFromMin="2017-04-14T00:00:00" dateFromMax="2017-04-14T00:00:00" dateToMin="2017-04-17T00:00:00" dateToMax="2017-04-17T00:00:00" duration="3" status="OK" person_price="-12" total_price="-12" currency="PLN">
      <debug orgPrice="" priceFromBa="" priceFromBawithMargin="" priceFromBawithoutMargin="" decodeString="" orgOper="" currConvert=""/>
      <id>6</id>
      <co de>KUBS</cod e>
      <type>INSURANCE</type>
      <codeElemType>A</codeElemType>
      <codeOptServType>X</codeOptServType>
      <packageType>NotSet</packageType>
      <date_from>14.04.2017</date_from>
      <date_to>17.04.2017</date_to>
      <checked>1</checked>
      <allocations>
        <allocate type="checkbox">
          <value>1</value>
          <checked>1</checked>
          <options_list/>
        </allocate>
      </allocations>
      <prices/>
      <hints/>
      <options/>
    </service>
    <service desc="Obowiazkowa oplata lotniskowa" dateFromMin="2017-04-14T00:00:00" dateFromMax="2017-04-14T00:00:00" dateToMin="2017-04-17T00:00:00" dateToMax="2017-04-17T00:00:00" duration="3" status="OK" person_price="300" total_price="300" currency="PLN">
      <debug orgPrice="" priceFromBa="" priceFromBawithMargin="" priceFromBawithoutMargin="" decodeString="" orgOper="" currConvert=""/>
      <id>7</id>
      <co de>PAR_DP_LOT</co de>
      <type>Transfer</type>
      <codeElemType>A</codeElemType>
      <codeOptServType>X</codeOptServType>
      <packageType>NotSet</packageType>
      <date_from>14.04.2017</date_from>
      <date_to>17.04.2017</date_to>
      <checked>1</checked>
      <allocations>
        <allocate type="checkbox">
          <value>1</value>
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
      <person price="1509" currency="PLN">
        <person_id type="integer">1</person_id>
        <gender type="selected" required="1">
          <option value="H" selected="1">Pan</option>
          <option value="F">Pani</option>
          <option value="I">Niemowlę</option>
          <option value="K">Dziecko</option>
        </gender>
        <firstname type="string" required="1">TEST</firstname>
        <secondname type="string"/>
        <lastname type="string" required="1">TEST</lastname>
        <birthdate type="date" required="1">17.06.1986</birthdate>
        <citizenship type="string"/>
        <passport_number type="string" required="1">45435</passport_number>
        <country_code type="selected">
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
          <option value="CN">Chiny</option>
          <option value="HK">Chiny</option>
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
          <option value="FK">Falkland Islands (Malvinas)</option>
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
          <option value="JE">Jersey</option>
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
          <option value="MO">Macao</option>
          <option value="MK">Macedonia</option>
          <option value="MG">Madagaskar</option>
          <option value="MW">Malawi</option>
          <option value="MV">Malediwy</option>
          <option value="MY">Malezja</option>
          <option value="ML">Mali</option>
          <option value="MT">Malta</option>
          <option value="MP">Mariany Północne</option>
          <option value="MA">Maroko</option>
          <option value="EH">Maroko</option>
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
          <option value="VI">U.S. Virgin Islands</option>
          <option value="UG">Uganda</option>
          <option value="UA">Ukraina</option>
          <option value="UY">Urugwaj</option>
          <option value="UZ">Uzbekistan</option>
          <option value="VU">Vanuatu</option>
          <option value="VE">Wenezuela</option>
          <option value="GB">Wielka Brytania</option>
          <option value="VN">Wietnam</option>
          <option value="CI">Wybrzeże Kości Słoniowej</option>
          <option value="CK">Wyspy Cooka</option>
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
        <city type="string">TEST1</city>
        <address type="string">TEST1</address>
        <post_city type="string"/>
        <post_code type="string">12-345</post_code>
        <phone type="string"/>
        <email_address type="string"/>
      </person>
    </persons>
    <client>
      <type type="string" required="1">person</type>
      <gender type="selected" required="1">
        <option value="H" selected="1">Pan</option>
        <option value="F">Pani</option>
      </gender>
      <firstname type="string" required="1">TEST</firstname>
      <lastname type="string" required="1">TEST</lastname>
      <birthdate type="date">17.06.1986</birthdate>
      <post_code type="string" required="1">12-345</post_code>
      <post_city type="string" required="1">TEST</post_city>
      <country type="string"/>
      <city type="string" required="1">TEST</city>
      <address type="string" required="1">TEST</address>
      <phone type="string" required="1">5353</phone>
      <email_address type="string" required="1">TEST@TEST.PL</email_address>
      <taxidentifier type="string" required="1"/>
    </client>
    <formalAgreements>
      <formalAgreement>
        <co de>UKE</c ode>
        <desc>Zgoda na otrzymywanie informacji handlowych drogą elektroniczną.</desc>
      </formalAgreement>
      <formalAgreement required="1">
        <co de>GIODO</cod e>
        <desc>Zgoda na przetwarzanie danych osobowych w celu realizacji umowy.</desc>
        <selected>0</selected>
      </formalAgreement>
    </formalAgreements>
    <services_optional/>
  </forminfo>
  <payment>
    <requirements>
      <requirement>
        <type>BookingFee</type>
        <amount>0</amount>
        <paidAmount>0</paidAmount>
        <requiredTill>2016-06-17T11:10:23</requiredTill>
        <additionalInfo/>
      </requirement>
      <requirement>
        <type>PrePayment</type>
        <amount>302</amount>
        <paidAmount>0</paidAmount>
        <requiredTill>2016-06-18T11:10:23</requiredTill>
        <additionalInfo/>
      </requirement>
      <requirement>
        <type>RestPayment</type>
        <amount>1207</amount>
        <paidAmount>0</paidAmount>
        <requiredTill>2017-03-15T00:00:00</requiredTill>
        <additionalInfo/>
      </requirement>
    </requirements>
    <touroperatorInfo>
      <bankAccount>03 1030 1999 7797 5000 0101 0060</bankAccount>
      <bankName>Bank Handlowy w Warszawie S.A.</bankName>
      <name>Nowa itaka Sp. z o.o.</name>
      <address>Reymonta</address>
      <streetNo>39</streetNo>
      <streetPlaceNo/>
      <postCode>45-072</postCode>
      <city>Opole</city>
      <emailAddress>INFO@ITAKA.PL</emailAddress>
      <phoneNo>77 541 22 69</phoneNo>
      <faxNo>77 541 24 69</faxNo>
    </touroperatorInfo>
  </payment>
</response>

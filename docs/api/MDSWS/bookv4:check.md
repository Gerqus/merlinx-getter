## POST /bookv4/check

### Request

Type:
| Element name | Description |
| --- | --- |
| check | check if a booking can be made with given data |

Conditions:
| Element name | Description |
| --- | --- |
| ofr_tourOp | offer tourop |
| ofr_id | offer id |
| par_adt | number of adults |
| par_chd | number of childs |
| par_inf | number of infants |
| services_optional | list of optional services |
| currency | currency |
| language | language |
| extraHotel | Selected extrahotel. Required for 2in1 or Z+W offers. Format:  <extraHotel>  <htlCode>1000</htlCode>  <htlRoomCode>DBL</htlRoomCode>  <htlSrvCode>H</htlSrvCode>  <fromDate>2020-10-27</fromDate>  <toDate>2020-10-30</toDate>  </extraHotel> |

```xml
<?xml version="1.0" encoding="UTF-8"?>
<mds>
    <auth>
        <login>login</login>
        <pass>password</pass>
        <source>MDSWS</source>
        <srcDomain>test.mydomain.pl</srcDomain>
        <consultant>TEST</consultant>
    </auth>
    <request>
        <forminfo>
            <persons>
                <person int="0">
                    <gender>H</gender>
                    <lastname/>
                    <firstname/>
                    <birthdate>06.09.1989</birthdate>
                </person>
                <person int="1">
                    <gender>F</gender>
                    <lastname/>
                    <firstname/>
                    <birthdate>06.09.1989</birthdate>
                </person>
            </persons>
            <client/>
            <services_optional>
                <service int="0">
                    <codeElemType/>
                    <codeOptServType>A</codeOptServType>
                    <code>11221</code>
                    <type>V</type>
                    <checked>0</checked>
                    <allocations>
                        <allocate int="0">
                            <value>1</value>
                            <checked>0</checked>
                        </allocate>
                        <allocate int="1">
                            <value>2</value>
                            <checked>0</checked>
                        </allocate>
                    </allocations>
                </service>
                <service int="1">
                    <codeElemType/>
                    <codeOptServType>A</codeOptServType>
                    <code>10587</code>
                    <type>V</type>
                    <checked>0</checked>
                    <allocations>
                        <allocate int="0">
                            <value>1</value>
                            <checked>0</checked>
                        </allocate>
                        <allocate int="1">
                            <value>2</value>
                            <checked>0</checked>
                        </allocate>
                    </allocations>
                </service>
                <service int="2">
                    <codeElemType/>
                    <codeOptServType>A</codeOptServType>
                    <code>11850</code>
                    <type>V</type>
                    <checked>0</checked>
                    <allocations>
                        <allocate int="0">
                            <value>1</value>
                            <checked>0</checked>
                        </allocate>
                        <allocate int="1">
                            <value>2</value>
                            <checked>0</checked>
                        </allocate>
                    </allocations>
                </service>
                <service int="3">
                    <codeElemType/>
                    <codeOptServType>A</codeOptServType>
                    <code>11846</code>
                    <type>V</type>
                    <checked>0</checked>
                    <allocations>
                        <allocate int="0">
                            <value>1</value>
                            <checked>0</checked>
                        </allocate>
                        <allocate int="1">
                            <value>2</value>
                            <checked>0</checked>
                        </allocate>
                    </allocations>
                </service>
                <service int="4">
                    <codeElemType/>
                    <codeOptServType>A</codeOptServType>
                    <code>11848</code>
                    <type>V</type>
                    <checked>1</checked>
                    <allocations>
                        <allocate int="0">
                            <value>1</value>
                            <checked>1</checked>
                        </allocate>
                        <allocate int="1">
                            <value>2</value>
                            <checked>1</checked>
                        </allocate>
                    </allocations>
                </service>
                <service int="5">
                    <codeElemType/>
                    <codeOptServType>A</codeOptServType>
                    <code>11222</code>
                    <type>V</type>
                    <checked>0</checked>
                    <allocations>
                        <allocate int="0">
                            <value>1</value>
                            <checked>0</checked>
                        </allocate>
                        <allocate int="1">
                            <value>2</value>
                            <checked>0</checked>
                        </allocate>
                    </allocations>
                </service>
                <service int="6">
                    <codeElemType/>
                    <codeOptServType>A</codeOptServType>
                    <code>10586</code>
                    <type>V</type>
                    <checked>0</checked>
                    <allocations>
                        <allocate int="0">
                            <value>1</value>
                            <checked>0</checked>
                        </allocate>
                        <allocate int="1">
                            <value>2</value>
                            <checked>0</checked>
                        </allocate>
                    </allocations>
                </service>
                <service int="7">
                    <codeElemType/>
                    <codeOptServType>A</codeOptServType>
                    <code>10590</code>
                    <type>V</type>
                    <checked>0</checked>
                    <allocations>
                        <allocate int="0">
                            <value>1</value>
                            <checked>0</checked>
                        </allocate>
                        <allocate int="1">
                            <value>2</value>
                            <checked>0</checked>
                        </allocate>
                    </allocations>
                </service>
                <service int="8">
                    <codeElemType/>
                    <codeOptServType>A</codeOptServType>
                    <code>11364</code>
                    <type>V</type>
                    <checked>0</checked>
                    <allocations>
                        <allocate int="0">
                            <value>1</value>
                            <checked>0</checked>
                        </allocate>
                        <allocate int="1">
                            <value>2</value>
                            <checked>0</checked>
                        </allocate>
                    </allocations>
                </service>
                <service int="9">
                    <codeElemType/>
                    <codeOptServType>A</codeOptServType>
                    <code>11201</code>
                    <type>V</type>
                    <checked>0</checked>
                    <allocations>
                        <allocate int="0">
                            <value>1</value>
                            <checked>0</checked>
                        </allocate>
                        <allocate int="1">
                            <value>2</value>
                            <checked>0</checked>
                        </allocate>
                    </allocations>
                </service>
                <service int="10">
                    <codeElemType/>
                    <codeOptServType>A</codeOptServType>
                    <code>11363</code>
                    <type>V</type>
                    <checked>0</checked>
                    <allocations>
                        <allocate int="0">
                            <value>1</value>
                            <checked>0</checked>
                        </allocate>
                        <allocate int="1">
                            <value>2</value>
                            <checked>0</checked>
                        </allocate>
                    </allocations>
                </service>
                <service int="11">
                    <codeElemType/>
                    <codeOptServType>A</codeOptServType>
                    <code>RG</code>
                    <type>V</type>
                    <checked>0</checked>
                    <allocations>
                        <allocate int="0">
                            <value>1</value>
                            <checked>0</checked>
                        </allocate>
                        <allocate int="1">
                            <value>2</value>
                            <checked>0</checked>
                        </allocate>
                    </allocations>
                </service>
            </services_optional>
            <includeTFGservice>0</includeTFGservice>
        </forminfo>
        <conditions>
            <language>PL</language>
            <ofr_tourOp>EXIM</ofr_tourOp>
            <ofr_id>f38f93bc76e9425f46045030ad0dac645cbacebfe13cc7b10695084e4439917a</ofr_id>
            <par_adt>2</par_adt>
            <par_chd>0</par_chd>
            <par_inf>0</par_inf>
            <currency>PLN</currency>
        </conditions>
        <type>check</type>
    </request>
</mds>
```

### Answer

```xml
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<response>
    <result msgCode="726" status="OK" msgText="Rezerwacja możliwa, proszę uzupełnić dane." orgOperText="" subCode=""/>
    <agency_messages>
        <agency_message type="booking" status="OK">Mozna smialo rezerwowac!</agency_message>
    </agency_messages>
    <option possible="1" optiondate="2019-09-08" optiontime="09:29:07"/>
    <pricetotal operPrice="4366.00" operCurrency="PLN" TFGIncluded="0"/>
    <catalog></catalog>
    <hotel code="24295" name="Amwaj Blue Beach Resort  Spa Soma Bay" roomCode="09I" roomDesc="standard dbl" mealCode="A"
           mealDesc="All Inclusive" city="Soma Bay" country="Egipt" category="50" depDate="" desDate="" status=""
           price="" currency="" tourop="" bookingNr=""/>
    <transport type="F" carrierCode="CHR" carrierDesc="" depCode="WAW" desCode="HRG" depDate="2019-12-12"
               desDate="2019-12-12" rDepCode="HRG" rDesCode="WAW" rDepDate="2019-12-19" rDesDate="2019-12-19"
               depTime="00:00" desTime="00:00" rDepTime="00:00" rDesTime="00:00" flightNo="" rFlightNo="" status=""
               price="" currency="" handBaggage="" rHandBaggage="" regBaggage="" rRegBaggage="">
        <flightOut>
            <flight depCode="WAW" depName="Warszawa" desCode="HRG" desName="" depDate="2019-12-12" depTime="00:00"
                    desDate="2019-12-12" desTime="00:00" flightNo=""/>
        </flightOut>
        <flightRet>
            <flight depCode="HRG" depName="" desCode="WAW" desName="" depDate="2019-12-19" depTime="00:00"
                    desDate="2019-12-19" desTime="00:00" flightNo=""/>
        </flightRet>
    </transport>
    <services>
        <service desc="Gwarancja Niezmienności Ceny - Gratis" optionalServiceType="additional" perPassenger="0"
                 perBooking="1">
            <debug></debug>
            <id>1</id>
            <code>11348</code>
            <type>V</type>
            <codeOptServType>A</codeOptServType>
            <excludedCodes></excludedCodes>
            <requiredCodes></requiredCodes>
            <merlinGroupId>10</merlinGroupId>
            <merlinGroupName>Pozostałe</merlinGroupName>
            <checked>0</checked>
            <flightData></flightData>
            <allocations>
                <allocate type="checkbox">
                    <value>1</value>
                    <checked>0</checked>
                    <options_list></options_list>
                </allocate>
                <allocate type="checkbox">
                    <value>2</value>
                    <checked>0</checked>
                    <options_list></options_list>
                </allocate>
            </allocations>
            <prices></prices>
            <hints></hints>
            <options></options>
        </service>
        <service desc="Transfer (airport/hotel/airport)" optionalServiceType="additional" perPassenger="0"
                 perBooking="1">
            <debug></debug>
            <id>2</id>
            <code>11963</code>
            <type>V</type>
            <codeOptServType>A</codeOptServType>
            <excludedCodes></excludedCodes>
            <requiredCodes></requiredCodes>
            <checked>0</checked>
            <flightData></flightData>
            <allocations>
                <allocate type="checkbox">
                    <value>1</value>
                    <checked>0</checked>
                    <options_list></options_list>
                </allocate>
                <allocate type="checkbox">
                    <value>2</value>
                    <checked>0</checked>
                    <options_list></options_list>
                </allocate>
            </allocations>
            <prices></prices>
            <hints></hints>
            <options></options>
        </service>
        <service desc="Turystyczny Fundusz Gwarancyjny - 15 PLN/osoba" optionalServiceType="additional" perPassenger="0"
                 perBooking="1">
            <debug></debug>
            <id>3</id>
            <code>11526</code>
            <type>V</type>
            <codeOptServType>A</codeOptServType>
            <excludedCodes></excludedCodes>
            <requiredCodes></requiredCodes>
            <merlinGroupId>10</merlinGroupId>
            <merlinGroupName>Pozostałe</merlinGroupName>
            <checked>0</checked>
            <flightData></flightData>
            <allocations>
                <allocate type="checkbox">
                    <value>1</value>
                    <checked>0</checked>
                    <options_list></options_list>
                </allocate>
                <allocate type="checkbox">
                    <value>2</value>
                    <checked>0</checked>
                    <options_list></options_list>
                </allocate>
            </allocations>
            <prices></prices>
            <hints></hints>
            <options></options>
        </service>
        <service desc="Ubezpieczenie następstw chorób przewlekłych - GRATIS" optionalServiceType="additional"
                 perPassenger="0" perBooking="1">
            <debug></debug>
            <id>6</id>
            <code>10319</code>
            <type>V</type>
            <codeOptServType>A</codeOptServType>
            <excludedCodes></excludedCodes>
            <requiredCodes></requiredCodes>
            <merlinGroupId>1</merlinGroupId>
            <merlinGroupName>Ubezpieczenia</merlinGroupName>
            <checked>0</checked>
            <flightData></flightData>
            <allocations>
                <allocate type="checkbox">
                    <value>1</value>
                    <checked>0</checked>
                    <options_list></options_list>
                </allocate>
                <allocate type="checkbox">
                    <value>2</value>
                    <checked>0</checked>
                    <options_list></options_list>
                </allocate>
            </allocations>
            <prices></prices>
            <hints></hints>
            <options></options>
        </service>
        <service
                desc="Ubezpieczenie podstawowe- KL, NNW, bagażu, następstw chorób przewlekłych - ERGO Ubezpieczenia Podróży"
                optionalServiceType="additional" perPassenger="0" perBooking="1">
            <debug></debug>
            <id>17</id>
            <code>10585</code>
            <type>V</type>
            <codeOptServType>A</codeOptServType>
            <excludedCodes></excludedCodes>
            <requiredCodes></requiredCodes>
            <merlinGroupId>0</merlinGroupId>
            <checked>0</checked>
            <flightData></flightData>
            <allocations>
                <allocate type="checkbox">
                    <value>1</value>
                    <checked>0</checked>
                    <options_list></options_list>
                </allocate>
                <allocate type="checkbox">
                    <value>2</value>
                    <checked>0</checked>
                    <options_list></options_list>
                </allocate>
            </allocations>
            <prices></prices>
            <hints></hints>
            <options></options>
        </service>
        <service desc="" currency="PLN">
            <debug></debug>
            <code>WAWHRG HRGWAW</code>
            <type>F</type>
            <excludedCodes></excludedCodes>
            <requiredCodes></requiredCodes>
            <date_from>12.12.2019</date_from>
            <date_to>19.12.2019</date_to>
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
        <service desc="" currency="PLN">
            <debug></debug>
            <code>24295</code>
            <type>H</type>
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
        <service desc="Ubezpieczenie od Skutków Ataku Terrorystycznego_ROZSZERZONE - ERGO Ubezpieczenia Podróży"
                 optionalServiceType="additional" perPassenger="1" perBooking="0" total_price="80" currency="PLN">
            <debug></debug>
            <id>10</id>
            <code>11848</code>
            <type>V</type>
            <codeOptServType>A</codeOptServType>
            <group>Ubezpieczenie_ROZSZERZONE</group>
            <excludedCodes></excludedCodes>
            <requiredCodes></requiredCodes>
            <merlinGroupId>0</merlinGroupId>
            <checked>1</checked>
            <flightData></flightData>
            <allocations>
                <allocate type="checkbox">
                    <value>1</value>
                    <checked>1</checked>
                    <options_list></options_list>
                </allocate>
                <allocate type="checkbox">
                    <value>2</value>
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
            <person price="2183" currency="PLN">
                <person_id type="integer">1</person_id>
                <gender type="selected" maxlength="1" name="Płeć" required="1" requiredOption="1">
                    <option value="H" selected="1">Pan</option>
                    <option value="F">Pani</option>
                </gender>
                <lastname type="string" maxlength="30" name="Nazwisko" required="1" requiredOption="1"></lastname>
                <firstname type="string" maxlength="30" name="Imię" required="1" requiredOption="1"></firstname>
                <birthdate type="date" maxlength="10" name="Data urodzenia" required="1" requiredOption="1">06.09.1989
                </birthdate>
                <passport_number type="string" maxlength="20" name="Paszport" required="0"
                                 requiredOption="0"></passport_number>
                <post_code type="postcode" maxlength="6" name="Kod" required="1" requiredOption="1"></post_code>
                <city type="string" maxlength="30" name="Miejscowość" required="1" requiredOption="1"></city>
                <address type="string" maxlength="30" name="Ulica" required="1" requiredOption="1"></address>
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
                <phone type="phone" maxlength="12" name="Telefon" required="0" requiredOption="0"></phone>
                <email_address type="email" maxlength="50" name="E-mail" required="0"
                               requiredOption="0"></email_address>
            </person>
            <person price="2183" currency="PLN">
                <person_id type="integer">2</person_id>
                <gender type="selected" maxlength="1" name="Płeć" required="1" requiredOption="1">
                    <option value="H">Pan</option>
                    <option value="F" selected="1">Pani</option>
                </gender>
                <lastname type="string" maxlength="30" name="Nazwisko" required="1" requiredOption="1"></lastname>
                <firstname type="string" maxlength="30" name="Imię" required="1" requiredOption="1"></firstname>
                <birthdate type="date" maxlength="10" name="Data urodzenia" required="1" requiredOption="1">06.09.1989
                </birthdate>
                <passport_number type="string" maxlength="20" name="Paszport" required="0"
                                 requiredOption="0"></passport_number>
                <post_code type="postcode" maxlength="6" name="Kod" required="1" requiredOption="1"></post_code>
                <city type="string" maxlength="30" name="Miejscowość" required="1" requiredOption="1"></city>
                <address type="string" maxlength="30" name="Ulica" required="1" requiredOption="1"></address>
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
                <phone type="phone" maxlength="12" name="Telefon" required="0" requiredOption="0"></phone>
                <email_address type="email" maxlength="50" name="E-mail" required="0"
                               requiredOption="0"></email_address>
            </person>
        </persons>
        <client>
            <title type="selected" maxlength="1" name="Płeć" required="0" requiredOption="0">
                <option value="H">Pan</option>
                <option value="F">Pani</option>
            </title>
            <lastname type="string" maxlength="30" name="Nazwisko" required="1" requiredOption="1"></lastname>
            <firstname type="string" maxlength="30" name="Imię" required="1" requiredOption="1"></firstname>
            <address type="string" maxlength="30" name="Ulica" required="1" requiredOption="1"></address>
            <post_code type="postcode" maxlength="6" name="Kod" required="1" requiredOption="1"></post_code>
            <city type="string" maxlength="30" name="Miejscowość" required="1" requiredOption="1"></city>
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
            <phone type="phone" maxlength="12" name="Telefon" required="1" requiredOption="1"></phone>
            <email_address type="email" maxlength="50" name="E-mail" required="1" requiredOption="1"></email_address>
            <birthdate type="date" maxlength="10" name="Data urodzenia" required="0" requiredOption="0"></birthdate>
        </client>
        <services_optional groupsInclusive="1">
            <service
                    desc="Ubezpieczenie Optymalne - KL, NNW, bagażu, następstw chorób przewlekłych - ERGO Ubezpieczenia Podróży"
                    optionalServiceType="additional" perPassenger="1" perBooking="0" currency="PLN">
                <debug></debug>
                <id>4</id>
                <code>11221</code>
                <type>V</type>
                <codeOptServType>A</codeOptServType>
                <group>Ubezpieczenie_OPTYMALNE</group>
                <excludedCodes>
                    <code>10587</code>
                    <code>11848</code>
                    <code>10590</code>
                    <code>11363</code>
                    <code>11846</code>
                    <code>10586</code>
                    <code>11201</code>
                </excludedCodes>
                <requiredCodes></requiredCodes>
                <merlinGroupId>0</merlinGroupId>
                <checked>0</checked>
                <flightData></flightData>
                <allocations>
                    <allocate type="checkbox">
                        <value>1</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                    <allocate type="checkbox">
                        <value>2</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                </allocations>
                <prices></prices>
                <hints></hints>
                <options></options>
            </service>
            <service
                    desc="Ubezpieczenie Rozszerzone - KL, NNW, bagażu, następstw chorób przewlekłych - ERGO Ubezpieczenia Podróży"
                    optionalServiceType="additional" perPassenger="1" perBooking="0" total_price="88" currency="PLN">
                <debug></debug>
                <id>5</id>
                <code>10587</code>
                <type>V</type>
                <codeOptServType>A</codeOptServType>
                <group>Ubezpieczenie_ROZSZERZONE</group>
                <excludedCodes>
                    <code>11846</code>
                    <code>10586</code>
                    <code>11201</code>
                    <code>11221</code>
                    <code>11850</code>
                    <code>11222</code>
                    <code>11364</code>
                </excludedCodes>
                <requiredCodes></requiredCodes>
                <merlinGroupId>0</merlinGroupId>
                <checked>0</checked>
                <flightData></flightData>
                <allocations>
                    <allocate type="checkbox">
                        <value>1</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                    <allocate type="checkbox">
                        <value>2</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                </allocations>
                <prices></prices>
                <hints></hints>
                <options></options>
            </service>
            <service desc="Ubezpieczenie od Rezygnacji" optionalServiceType="additional" perPassenger="1" perBooking="0"
                     currency="PLN">
                <debug></debug>
                <id>7</id>
                <code>RG</code>
                <type>V</type>
                <codeOptServType>A</codeOptServType>
                <excludedCodes></excludedCodes>
                <requiredCodes></requiredCodes>
                <merlinGroupId>3</merlinGroupId>
                <merlinGroupName>Ubezpieczenia od rezygnacji</merlinGroupName>
                <checked>0</checked>
                <flightData></flightData>
                <allocations>
                    <allocate type="checkbox">
                        <value>1</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                    <allocate type="checkbox">
                        <value>2</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                </allocations>
                <prices></prices>
                <hints></hints>
                <options></options>
            </service>
            <service desc="Ubezpieczenie od Skutków Ataku Terrorystycznego_OPTYMALNE - ERGO Ubezpieczenia Podróży"
                     optionalServiceType="additional" perPassenger="1" perBooking="0" currency="PLN">
                <debug></debug>
                <id>8</id>
                <code>11850</code>
                <type>V</type>
                <codeOptServType>A</codeOptServType>
                <group>Ubezpieczenie_OPTYMALNE</group>
                <excludedCodes>
                    <code>10587</code>
                    <code>11848</code>
                    <code>10590</code>
                    <code>11363</code>
                    <code>11846</code>
                    <code>10586</code>
                    <code>11201</code>
                </excludedCodes>
                <requiredCodes></requiredCodes>
                <merlinGroupId>0</merlinGroupId>
                <checked>0</checked>
                <flightData></flightData>
                <allocations>
                    <allocate type="checkbox">
                        <value>1</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                    <allocate type="checkbox">
                        <value>2</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                </allocations>
                <prices></prices>
                <hints></hints>
                <options></options>
            </service>
            <service desc="Ubezpieczenie od Skutków Ataku Terrorystycznego_PODSTAWOWE - ERGO Ubezpieczenia Podróży"
                     optionalServiceType="additional" perPassenger="1" perBooking="0" currency="PLN">
                <debug></debug>
                <id>9</id>
                <code>11846</code>
                <type>V</type>
                <codeOptServType>A</codeOptServType>
                <group>Ubezpieczenie_PODSTAWOWE</group>
                <excludedCodes>
                    <code>11221</code>
                    <code>11850</code>
                    <code>11222</code>
                    <code>11364</code>
                    <code>10587</code>
                    <code>11848</code>
                    <code>10590</code>
                    <code>11363</code>
                </excludedCodes>
                <requiredCodes></requiredCodes>
                <merlinGroupId>0</merlinGroupId>
                <checked>0</checked>
                <flightData></flightData>
                <allocations>
                    <allocate type="checkbox">
                        <value>1</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                    <allocate type="checkbox">
                        <value>2</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                </allocations>
                <prices></prices>
                <hints></hints>
                <options></options>
            </service>
            <service desc="Ubezpieczenie od Skutków Ataku Terrorystycznego_ROZSZERZONE - ERGO Ubezpieczenia Podróży"
                     optionalServiceType="additional" perPassenger="1" perBooking="0" total_price="80" currency="PLN">
                <debug></debug>
                <id>10</id>
                <code>11848</code>
                <type>V</type>
                <codeOptServType>A</codeOptServType>
                <group>Ubezpieczenie_ROZSZERZONE</group>
                <excludedCodes>
                    <code>11846</code>
                    <code>10586</code>
                    <code>11201</code>
                    <code>11221</code>
                    <code>11850</code>
                    <code>11222</code>
                    <code>11364</code>
                </excludedCodes>
                <requiredCodes></requiredCodes>
                <merlinGroupId>0</merlinGroupId>
                <checked>1</checked>
                <flightData></flightData>
                <allocations>
                    <allocate type="checkbox">
                        <value>1</value>
                        <checked>1</checked>
                        <options_list></options_list>
                    </allocate>
                    <allocate type="checkbox">
                        <value>2</value>
                        <checked>1</checked>
                        <options_list></options_list>
                    </allocate>
                </allocations>
                <prices></prices>
                <hints></hints>
                <options></options>
            </service>
            <service desc="Ubezpieczenie od Sportów Wysokiego Ryzyka OPTYMALNE - ERGO Ubezpieczenia Podróży"
                     optionalServiceType="additional" perPassenger="1" perBooking="0" currency="PLN">
                <debug></debug>
                <id>11</id>
                <code>11222</code>
                <type>V</type>
                <codeOptServType>A</codeOptServType>
                <group>Ubezpieczenie_OPTYMALNE</group>
                <excludedCodes>
                    <code>10587</code>
                    <code>11848</code>
                    <code>10590</code>
                    <code>11363</code>
                    <code>11846</code>
                    <code>10586</code>
                    <code>11201</code>
                </excludedCodes>
                <requiredCodes></requiredCodes>
                <merlinGroupId>0</merlinGroupId>
                <checked>0</checked>
                <flightData></flightData>
                <allocations>
                    <allocate type="checkbox">
                        <value>1</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                    <allocate type="checkbox">
                        <value>2</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                </allocations>
                <prices></prices>
                <hints></hints>
                <options></options>
            </service>
            <service desc="Ubezpieczenie od Sportów Wysokiego Ryzyka_PODSTAWOWE - ERGO Ubezpieczenia Podróży"
                     optionalServiceType="additional" perPassenger="1" perBooking="0" currency="PLN">
                <debug></debug>
                <id>12</id>
                <code>10586</code>
                <type>V</type>
                <codeOptServType>A</codeOptServType>
                <group>Ubezpieczenie_PODSTAWOWE</group>
                <excludedCodes>
                    <code>11221</code>
                    <code>11850</code>
                    <code>11222</code>
                    <code>11364</code>
                    <code>10587</code>
                    <code>11848</code>
                    <code>10590</code>
                    <code>11363</code>
                </excludedCodes>
                <requiredCodes></requiredCodes>
                <merlinGroupId>0</merlinGroupId>
                <checked>0</checked>
                <flightData></flightData>
                <allocations>
                    <allocate type="checkbox">
                        <value>1</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                    <allocate type="checkbox">
                        <value>2</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                </allocations>
                <prices></prices>
                <hints></hints>
                <options></options>
            </service>
            <service desc="Ubezpieczenie od Sportów Wysokiego Ryzyka_ROZSZERZONE - ERGO Ubezpieczenia Podróży"
                     optionalServiceType="additional" perPassenger="1" perBooking="0" currency="PLN">
                <debug></debug>
                <id>13</id>
                <code>10590</code>
                <type>V</type>
                <codeOptServType>A</codeOptServType>
                <group>Ubezpieczenie_ROZSZERZONE</group>
                <excludedCodes>
                    <code>11846</code>
                    <code>10586</code>
                    <code>11201</code>
                    <code>11221</code>
                    <code>11850</code>
                    <code>11222</code>
                    <code>11364</code>
                </excludedCodes>
                <requiredCodes></requiredCodes>
                <merlinGroupId>0</merlinGroupId>
                <checked>0</checked>
                <flightData></flightData>
                <allocations>
                    <allocate type="checkbox">
                        <value>1</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                    <allocate type="checkbox">
                        <value>2</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                </allocations>
                <prices></prices>
                <hints></hints>
                <options></options>
            </service>
            <service
                    desc="Ubezpieczenie od rezygnacji z rozszerzeniem o następstwa chorób przewlekłych_OPTYMALNE - ERGO Ubezpieczenia Podróży"
                    optionalServiceType="additional" perPassenger="1" perBooking="0" currency="PLN">
                <debug></debug>
                <id>14</id>
                <code>11364</code>
                <type>V</type>
                <codeOptServType>A</codeOptServType>
                <group>Ubezpieczenie_OPTYMALNE</group>
                <excludedCodes>
                    <code>10587</code>
                    <code>11848</code>
                    <code>10590</code>
                    <code>11363</code>
                    <code>11846</code>
                    <code>10586</code>
                    <code>11201</code>
                </excludedCodes>
                <requiredCodes></requiredCodes>
                <merlinGroupId>0</merlinGroupId>
                <checked>0</checked>
                <flightData></flightData>
                <allocations>
                    <allocate type="checkbox">
                        <value>1</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                    <allocate type="checkbox">
                        <value>2</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                </allocations>
                <prices></prices>
                <hints></hints>
                <options></options>
            </service>
            <service
                    desc="Ubezpieczenie od rezygnacji z rozszerzeniem o następstwa chorób przewlekłych_PODSTAWOWE - ERGO Ubezpieczenia Podróży"
                    optionalServiceType="additional" perPassenger="1" perBooking="0" currency="PLN">
                <debug></debug>
                <id>15</id>
                <code>11201</code>
                <type>V</type>
                <codeOptServType>A</codeOptServType>
                <group>Ubezpieczenie_PODSTAWOWE</group>
                <excludedCodes>
                    <code>11221</code>
                    <code>11850</code>
                    <code>11222</code>
                    <code>11364</code>
                    <code>10587</code>
                    <code>11848</code>
                    <code>10590</code>
                    <code>11363</code>
                </excludedCodes>
                <requiredCodes></requiredCodes>
                <merlinGroupId>0</merlinGroupId>
                <checked>0</checked>
                <flightData></flightData>
                <allocations>
                    <allocate type="checkbox">
                        <value>1</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                    <allocate type="checkbox">
                        <value>2</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                </allocations>
                <prices></prices>
                <hints></hints>
                <options></options>
            </service>
            <service
                    desc="Ubezpieczenie od rezygnacji z rozszerzeniem o następstwa chorób przewlekłych_ROZSZERZONE - ERGO Ubezpieczenia Podróży"
                    optionalServiceType="additional" perPassenger="1" perBooking="0" currency="PLN">
                <debug></debug>
                <id>16</id>
                <code>11363</code>
                <type>V</type>
                <codeOptServType>A</codeOptServType>
                <group>Ubezpieczenie_ROZSZERZONE</group>
                <excludedCodes>
                    <code>11221</code>
                    <code>11850</code>
                    <code>11222</code>
                    <code>11364</code>
                    <code>11846</code>
                    <code>10586</code>
                    <code>11201</code>
                </excludedCodes>
                <requiredCodes></requiredCodes>
                <merlinGroupId>0</merlinGroupId>
                <checked>0</checked>
                <flightData></flightData>
                <allocations>
                    <allocate type="checkbox">
                        <value>1</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                    <allocate type="checkbox">
                        <value>2</value>
                        <checked>0</checked>
                        <options_list></options_list>
                    </allocate>
                </allocations>
                <prices></prices>
                <hints></hints>
                <options></options>
            </service>
        </services_optional>
        <loyaltyCards>
            <loyaltyCard>
                <code>LC</code>
                <value></value>
                <message></message>
                <desc>Karta Lojalnościowa</desc>
            </loyaltyCard>
        </loyaltyCards>
        <wishes></wishes>
        <formalAgreements>
            <formalAgreement required="1">
                <code>uwagi_1</code>
                <selected>0</selected>
                <desc>Oświadczam, że przed zawarciem Umowy zostały przekazane Płatnikowi rezerwacji standardowe
                    informacje Ustawy z dnia 24 listopada 2017.
                </desc>
            </formalAgreement>
            <formalAgreement required="0">
                <code>1</code>
                <desc>Zgadzam się na wykorzystywanie moich danych osobowych przez Exim S.A. w celach marketingowych,
                    zgodnie z rozporządzeniem Parlamentu Europejskiego i Rady (UE) 2016/679 z dnia 27 kwietnia 2016 r. w
                    sprawie ochrony osób fizycznych w związku z przetwarzaniem danych osobowych i w sprawie swobodnego
                    przepływu takich danych oraz uchylenia dyrektywy 95/46/WE. &#xD;&#xA;
                </desc>
            </formalAgreement>
            <formalAgreement required="0">
                <code>5</code>
                <desc>Zgadzam się na wykorzystywanie mojego adresu e-mail do przesyłania informacji handlowych, zgodnie
                    z ustawą z dnia 18 lipca 2002 r. o świadczeniu usług drogą elektroniczną
                </desc>
            </formalAgreement>
            <formalAgreement required="0">
                <code>2</code>
                <desc>Wyrażam zgodę na wykorzystywanie moich danych osobowych niezbędnych do realizacji programu &#34;NAJLEPSZE
                    WAKACJE - NAJLEPSZY KLIENT&#34;, zgodnie z rozporządzeniem Parlamentu Europejskiego i Rady (UE)
                    2016/679 z dnia 27 kwietnia 2016 r. w sprawie ochrony osób fizycznych w związku z przetwarzaniem
                    danych osobowych i w sprawie swobodnego przepływu takich danych oraz uchylenia dyrektywy 95/46/WE.
                </desc>
            </formalAgreement>
        </formalAgreements>
        <paymentTable></paymentTable>
        <externalServiceTable></externalServiceTable>
    </forminfo>
    <payment>
        <requirements>
            <requirement>
                <type>BookingFee</type>
                <amount>874.00</amount>
                <paidAmount>0</paidAmount>
                <requiredTill>2019-09-09</requiredTill>
                <additionalInfo></additionalInfo>
            </requirement>
            <requirement>
                <type>RestPayment</type>
                <amount>3493.00</amount>
                <paidAmount>0</paidAmount>
                <requiredTill>2019-11-21</requiredTill>
                <additionalInfo></additionalInfo>
            </requirement>
        </requirements>
        <touroperatorInfo>
            <bankAccount></bankAccount>
            <bankName></bankName>
            <name></name>
            <address></address>
            <streetNo></streetNo>
            <streetPlaceNo></streetPlaceNo>
            <postCode></postCode>
            <city></city>
            <emailAddress></emailAddress>
            <phoneNo></phoneNo>
            <faxNo></faxNo>
        </touroperatorInfo>
    </payment>
    <hints>
        <hint desc="Mozliwa rezerwacja opcjonalna do: 2019-09-08 09:29:07" type="hint"></hint>
        <hint desc="Opl. rez 874 PLN do 090919, zal. 0 PLN do 000000, reszta 3493 PLN do 211119" type="hint"></hint>
        <hint desc="CZ: [10319] Ubezpieczenie nastepstw chorob przewleklych - GRATIS" type="hint"></hint>
        <hint desc="[10585] Ubezpieczenie podstawowe- KL, NNW, bagazu, nastepstw chorob przewleklych - ERGO Ubezpieczenia Podrozy"
              type="hint"></hint>
        <hint desc="[11348] Gwarancja Niezmiennosci Ceny - Gratis" type="hint"></hint>
        <hint desc="[11526] Turystyczny Fundusz Gwarancyjny - 15 PLN/osoba" type="hint"></hint>
        <hint desc="[11963] Transfer (airport/hotel/airport)" type="hint"></hint>
        <hint desc=" OH:F38F935CBACE " type="remark"></hint>
    </hints>
</response>
```

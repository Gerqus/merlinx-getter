## POST http://mdsws.merlinx.pl/bookV4/bookingcancelcheck

### Request

Type:
| Element name | Description |
| --- | --- |
| bookingcancelcheck | check if a booking can be canceled |

Conditions:
| Element name | Description |
| --- | --- |
| ofr_tourOp | offer tourop |
| booking_number | booking number |
| language | language |

<?xml version="1.0" encoding="UTF-8"?>
<mds>
  <auth>
    <login>login</login>
    <pass>password</pass>
    <source>B2B</source>
    <srcDomain>test.mydomain.pl</srcDomain>
  </auth>
  <request>
       <type>bookingcancelcheck</type>
    <conditions>
      <language>PL</language>
      <ofr_tourOp>VITX</ofr_tourOp>
      <booking_number>1010024</booking_number>
    </conditions>
  </request>
</mds>

### Answer

<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<response>
  <result msgCode="760" bookingNumber="1010024" status="OP" msgText="Anulowanie rezerwacji mozliwe" orgOperText="Anulowanie rezerwacji mozliwe, zastosowac akcje 'S' / Anulacja rezerwacji jest mozliwa" subCode="999000244"/>
  <option possible="1" optiondate="2016-06-17" optiontime="23:59:59"/>
  <cancel possible="1"/>
  <pricetotal operPrice="0" operCurrency="PLN"/>
  <catalog>KN16</catalog>
  <prices>
    <margin>0.00</margin>
    <currency>PLN</currency>
  </prices>
  <hotel code="NCECITR" name="Nicea" roomCode="SGL" roomDesc="Pok. 1 os." mealCode="F" mealDesc="Śniadania" city="" country="Francja" category="" depDate="2016-08-05" desDate="2016-08-08" status="XX" price="" currency=""/>
  <transport type="F" carrierCode="" carrierDesc="" depCode="WAW" desCode="NCE" depDate="2016-08-05" desDate="2016-08-05" rDepCode="NCE" rDesCode="WAW" rDepDate="2016-08-08" rDesDate="2016-08-08" depTime="00:00" desTime="13:25" rDepTime="00:00" rDesTime="16:25" flightNo="341" rFlightNo="342" status="XX" price="" currency="">
    <flightOut>
      <flight depCode="WAW" depName="Warszawa" desCode="NCE" desName="Nice" depDate="2016-08-05" depTime="10:50" desDate="2016-08-05" desTime="13:25" flightNo="341"/>
    </flightOut>
    <flightRet>
      <flight depCode="NCE" depName="Nice" desCode="WAW" desName="Warszawa" depDate="2016-08-08" depTime="14:05" desDate="2016-08-08" desTime="16:25" flightNo="342"/>
    </flightRet>
  </transport>
  <services>
    <service desc="Bilety wstepu, lokalni przewodnicy itp. platne dodatkowo na miejscu. Orientacyjne koszty podane w opisie imprezy oraz na www.itaka.pl" dateFromMin="2016-08-05T00:00:00" dateFromMax="2016-08-05T00:00:00" dateToMin="2016-08-08T00:00:00" dateToMax="2016-08-08T00:00:00" duration="3" status="XX">
      <debug orgPrice="" priceFromBa="" priceFromBawithMargin="" priceFromBawithoutMargin="" decodeString="" orgOper="" currConvert=""/>
      <id>3</id>
      <co de>TEXT_BILET</co de>
      <type>Usługa automatyczna</type>
      <codeElemType>A</codeElemType>
      <codeOptServType>X</codeOptServType>
      <packageType>NotSet</packageType>
      <date_from>05.08.2016</date_from>
      <date_to>08.08.2016</date_to>
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
    <service desc="Obowiazkowa oplata transportowa" dateFromMin="2016-08-05T00:00:00" dateFromMax="2016-08-05T00:00:00" dateToMin="2016-08-08T00:00:00" dateToMax="2016-08-08T00:00:00" duration="3" status="XX">
      <debug orgPrice="" priceFromBa="" priceFromBawithMargin="" priceFromBawithoutMargin="" decodeString="" orgOper="" currConvert=""/>
      <id>4</id>
      <c ode>NCE_DP_TRA</co de>
      <type>Transfer</type>
      <codeElemType>A</codeElemType>
      <codeOptServType>X</codeOptServType>
      <packageType>NotSet</packageType>
      <date_from>05.08.2016</date_from>
      <date_to>08.08.2016</date_to>
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
    <service desc="Ubezpieczenie Itaka Simple. Obejmuje koszty leczenia do 15 000 EUR" dateFromMin="2016-08-05T00:00:00" dateFromMax="2016-08-05T00:00:00" dateToMin="2016-08-08T00:00:00" dateToMax="2016-08-08T00:00:00" duration="3" status="XX">
      <debug orgPrice="" priceFromBa="" priceFromBawithMargin="" priceFromBawithoutMargin="" decodeString="" orgOper="" currConvert=""/>
      <id>5</id>
      <co de>UBSKLNW</co de>
      <type>INSURANCE</type>
      <codeElemType>A</codeElemType>
      <codeOptServType>X</codeOptServType>
      <packageType>NotSet</packageType>
      <date_from>05.08.2016</date_from>
      <date_to>08.08.2016</date_to>
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
    <service desc="Ubezpieczenie wliczone w cene" dateFromMin="2016-08-05T00:00:00" dateFromMax="2016-08-05T00:00:00" dateToMin="2016-08-08T00:00:00" dateToMax="2016-08-08T00:00:00" duration="3" status="XX">
      <debug orgPrice="" priceFromBa="" priceFromBawithMargin="" priceFromBawithoutMargin="" decodeString="" orgOper="" currConvert=""/>
      <id>6</id>
      <c ode>KUBS</co de>
      <type>INSURANCE</type>
      <codeElemType>A</codeElemType>
      <codeOptServType>X</codeOptServType>
      <packageType>NotSet</packageType>
      <date_from>05.08.2016</date_from>
      <date_to>08.08.2016</date_to>
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
    <service desc="Obowiazkowa oplata lotniskowa" dateFromMin="2016-08-05T00:00:00" dateFromMax="2016-08-05T00:00:00" dateToMin="2016-08-08T00:00:00" dateToMax="2016-08-08T00:00:00" duration="3" status="XX">
      <debug orgPrice="" priceFromBa="" priceFromBawithMargin="" priceFromBawithoutMargin="" decodeString="" orgOper="" currConvert=""/>
      <id>7</id>
      <c ode>NCE_DP_LOT</co de>
      <type>Transfer</type>
      <codeElemType>A</codeElemType>
      <codeOptServType>X</codeOptServType>
      <packageType>NotSet</packageType>
      <date_from>05.08.2016</date_from>
      <date_to>08.08.2016</date_to>
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
</response>

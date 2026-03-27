## POST /bookv4/bookingcancel

### Request

Type:
| Element name | Description |
| --- | --- |
| bookingcancel | cancel a booking |

Conditions:
| Element name | Description |
| --- | --- |
| ofr_tourOp | offer tourop |
| language | language |
| booking_number | number of a booking |

```xml
<?xml version="1.0" encoding="UTF-8"?>
<mds>
  <auth>
    <login>login</login>
    <pass>password</pass>
    <source>MDSWS</source>
    <srcDomain>test.mydomain.pl</srcDomain>
  </auth>
  <request>
    <conditions>
      <language>PL</language>
      <ofr_tourOp>VITX</ofr_tourOp>
      <booking_number>1010060</booking_number>
    </conditions>
    </conditions>
    <type>bookingcancel</type>
  </request>
</mds>
```

### Answer

```xml
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<response>
  <result msgCode="202" bookingNumber="1010060" status="XX" msgText="Storno ok" orgOperText="Storno ok / Anulacja OK" subCode="999000247"/>
  <option possible="1" optiondate="2016-06-20" optiontime="13:00:00"/>
  <pricetotal operPrice="0" operCurrency="PLN"/>
  <catalog>KPZ3</catalog>
  <prices>
    <margin>0.00</margin>
    <currency>PLN</currency>
  </prices>
  <hotel code="PARCITR" name="Paryz" roomCode="SRD" roomDesc="Dokwaterowanie w pok. 2/3 os." mealCode="F" mealDesc="sniadania" city="" country="Francja" category="" depDate="2017-04-14" desDate="2017-04-17" status="XX" price="" currency="" tourop="" bookingNr=""/>
  <transport type="F" carrierCode="" carrierDesc="" depCode="WAW" desCode="PAR" depDate="2017-04-14" desDate="2017-04-14" rDepCode="PAR" rDesCode="WAW" rDepDate="2017-04-17" rDesDate="2017-04-17" depTime="00:00" desTime="00:00" rDepTime="00:00" rDesTime="00:00" flightNo="" rFlightNo="" status="XX" price="" currency="">
    <flightOut>
      <flight depCode="WAW" depName="Warszawa" desCode="PAR" desName="Paris" depDate="2017-04-14" depTime="00:00" desDate="2017-04-14" desTime="00:00" flightNo=""/>
    </flightOut>
    <flightRet>
      <flight depCode="PAR" depName="Paris" desCode="WAW" desName="Warszawa" depDate="2017-04-17" depTime="00:00" desDate="2017-04-17" desTime="00:00" flightNo=""/>
    </flightRet>
  </transport>
  <services>
    <service desc="Bilety wstepu, lokalni przewodnicy itp. platne dodatkowo na miejscu. Orientacyjne koszty podane w opisie imprezy oraz na www.itaka.pl" dateFromMin="2017-04-14T00:00:00" dateFromMax="2017-04-14T00:00:00" dateToMin="2017-04-17T00:00:00" dateToMax="2017-04-17T00:00:00" duration="3" status="XX">
      <debug orgPrice="" priceFromBa="" priceFromBawithMargin="" priceFromBawithoutMargin="" decodeString="" orgOper="" currConvert=""/>
      <id>3</id>
      <c ode>TEXT_BILET</co de>
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
    <service desc="Obowiazkowa oplata transportowa" dateFromMin="2017-04-14T00:00:00" dateFromMax="2017-04-14T00:00:00" dateToMin="2017-04-17T00:00:00" dateToMax="2017-04-17T00:00:00" duration="3" status="XX">
      <debug orgPrice="" priceFromBa="" priceFromBawithMargin="" priceFromBawithoutMargin="" decodeString="" orgOper="" currConvert=""/>
      <id>4</id>
      <co de>PAR_DP_TRA</c ode>
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
    <service desc="Ubezpieczenie Itaka Simple. Obejmuje koszty leczenia do 15 000 EUR" dateFromMin="2017-04-14T00:00:00" dateFromMax="2017-04-14T00:00:00" dateToMin="2017-04-17T00:00:00" dateToMax="2017-04-17T00:00:00" duration="3" status="XX">
      <debug orgPrice="" priceFromBa="" priceFromBawithMargin="" priceFromBawithoutMargin="" decodeString="" orgOper="" currConvert=""/>
      <id>5</id>
      <co de>UBSKLNW</co de>
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
    <service desc="Ubezpieczenie wliczone w cene" dateFromMin="2017-04-14T00:00:00" dateFromMax="2017-04-14T00:00:00" dateToMin="2017-04-17T00:00:00" dateToMax="2017-04-17T00:00:00" duration="3" status="XX">
      <debug orgPrice="" priceFromBa="" priceFromBawithMargin="" priceFromBawithoutMargin="" decodeString="" orgOper="" currConvert=""/>
      <id>6</id>
      <cod e>KUBS</c ode>
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
    <service desc="Obowiazkowa oplata lotniskowa" dateFromMin="2017-04-14T00:00:00" dateFromMax="2017-04-14T00:00:00" dateToMin="2017-04-17T00:00:00" dateToMax="2017-04-17T00:00:00" duration="3" status="XX">
      <debug orgPrice="" priceFromBa="" priceFromBawithMargin="" priceFromBawithoutMargin="" decodeString="" orgOper="" currConvert=""/>
      <id>7</id>
      <co de>PAR_DP_LOT</c ode>
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
</response>
```

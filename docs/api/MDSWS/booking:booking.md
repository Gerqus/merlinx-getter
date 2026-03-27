# Checking availability and booking

## General information

MDSWS maintains an internal data cache of operator data. This means that in the offers response, some prices will be out of date.

Once the end user has selected a offer, a check request must be submitted to MDSWS. This will cause a live request to be made to the operator and so an up-to-date price will be returned.

Some operators prices after the check reqest can change and this must be handled carefully. The user must be informed that the price of the offers has changed. There are a number of ways to handle this situation: The user can be instructed to repeat the search and booking process from the beginning. This is the simplest solution but the worst user experience.. Or This new price can then be displayed to the user and they can choose whether to continue or cancel the booking.

**In order to establish reservations and to check the possibility of selling, is required the activation of the tour operator. In the absence thereof, please contact our Merlin support from your own country or in case there is no office in your country at [merlinsupport@e-systemy.com](mailto:merlinsupport@e-systemy.com "merlinsupport@e-systemy.com")**

**In the exercise of reservation should not be used local language characters cause they may generate confusion with its setting.**

## Checking availability

Request a check is used to check the availability of the offer, calls it the basic price and [form field](/booking:fields "booking:fields") necessary to make reservations or options.

Terms of inquiries

| item name | Default value | Required | Description |
| --- | --- | --- | --- |
| [ofr_tourOp](/data:fields#ofr_tourop "data:fields") | absence | yes |     |
| [ofr_id](/data:fields#ofr_id "data:fields") | absence | yes |     |
| [par_adt](/data:fields#par_adt "data:fields") | absence | yes |     |
| [par_chd](/data:fields#par_chd "data:fields") | 0   | no  |     |
| [par_inf](/data:fields#par_inf "data:fields") | 0   | no  |     |
| expedient_code | absence | depending on the tour-operator | Agent(expedient) code |
| x_htl | absence | yes - at offers 7+7 | Code for the second part of your stay to offer a 7 +7.  <br>Format:  <br>[extra hotel id]_[hotel code]  <br>For example:  <br>5_LCAOASI/1/KOMB |
| [room_hash](/data:fields:names:room_hash "data:fields:names:room_hash") | absence | depending on the tour-operator | room id - needed by external providers data for example ( MRGO,TRHO) |

To check prices when include the age of the passengers / children should also send [form field](/booking:fields "booking:fields") with an array of [person](/booking:fields#person "booking:fields") supplemented with data of passengers (minimum gender code and date of birth of participants).

### Inquiry

<?xml version='1.0'?>
<mds>
  <auth>
    <login>login</login>
    <pass>password</pass>
  </auth>
  <request>
    <type>check</type>
    <conditions>      
      <par_adt>2</par_adt>
      <ofr_tourOp>ITAK</ofr_tourOp>      
      <ofr_id>3b9e9af3bea2b7e822df6805066de0cbc501a337f843d85689f84b47ac86e1f2</ofr_id>
    </conditions>
  </request>
</mds>

### Answer

<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<response>
	<offerstatus optionpossible="1" optiondate="2010-10-19" optiontime="14:52" status="BA"/>
	<pricetotal price="3778" curr="PLN"/>
	<hints cnt="9">
		<hint><![CDATA[Possible to book optional resa till: 19.10.2010 14:52]]></hint>
		<hint><![CDATA[WAWSSH/22.02.11,09:00-14:15 SSHWAW/02.03.11,01:50-05:10 /]]></hint>
		<hint><![CDATA[A : SSH_DP_LOT / Obligatory airport fee                            600.00]]></hint>
		<hint><![CDATA[A : SSH_DP_TRA / Transport obligatory surcharge                        380.00]]></hint>
		<hint><![CDATA[Hotel Resta Sharm / All inclusive / double room / Sharm El Sheikh / Egipt]]></hint>
		<hint><![CDATA[Hity Tygodnia]]></hint>
		<hint><![CDATA[A : KUBS / Insureance included in the price                                         -32.00]]></hint>
		<hint><![CDATA[A : UBSKLNW / Itaka simple insurance                                          32.00]]></hint>
		<hint><![CDATA[A : SSHTRAN / Transfer: airpoprt-hotel-airport]]></hint>
	</hints>
        <forminfo>
          <conditions>
            <type>checkbox</type>
            <checked>0</checked>
            <value>1</value>
          </conditions>
          <Person>
            <data>
              <lastname>
                <type>text</type>
                <value/>
              </lastname>
              <name>
                <type>text</type>
                <value/>
              </name>
              <birthdate>
                <type>text</type>
                <value>18.10.1980</value>
              </birthdate>
              <gender>
                <type>select</type>
                <desc>
                  <data>Pan</data>
                  <data>Pani</data>
                </desc>
                <values>
                  <data>H</data>
                  <data>F</data>
                </values>
                <selected>H</selected>
              </gender>
                (...)
            </data>
            <data>
              (...)
            </data>
          </Person>
      </forminfo>
</response>

## Booking

### Inguiry

Make your reservation by returning [form](/booking:fields "booking:fields") leaving only the selected values

<?xml version='1.0'?>
<mds>
  <auth>
    <login>login</login>
    <pass>password</pass>
  </auth>
  <request>
    <type>book</type>
    <conditions>
      <par_adt>2</par_adt>
      <ofr_tourOp>ITAK</ofr_tourOp>      
      <ofr_id>3b9e9af3bea2b7e822df6805066de0cbc501a337f843d85689f84b47ac86e1f2</ofr_id>
    </conditions>
    <forminfo>
      (...)
    </forminfo>
  </request>
</mds>

for example filled in section [forminfo](/booking:fields#forminfo "booking:fields"):

The type of booking made ​​(optional or constant) using the field controls <ReservationMode></ReservationMode> for option reservation is set to 0 for a fixed booking is set to 1.

<forminfo>
  <InternalAction>0</InternalAction>
  <DelPersonIdx>0</DelPersonIdx>
  <ReservationMode>0</ReservationMode>
  <check_price>0</check_price>
  <flaga>1</flaga>
  <unload_flag>1</unload_flag>
  <short_term>0</short_term>
  <additional_where_flag></additional_where_flag>
  <load_orderby>0</load_orderby>
  <hideDefBirthdates>1</hideDefBirthdates>
  <new_search></new_search>
  <check_payment_offer>0</check_payment_offer>
  <email_condition_checked>0</email_condition_checked>
  <client_radio></client_radio>
  <family_address></family_address>
  <test1_0>1</test1_0>
  <test1_1>2</test1_1>
  <test2_0>1</test2_0>
  <test2_1>2</test2_1>
  <test3_0>1</test3_0>
  <test3_1>2</test3_1>
  <test4_0>1</test4_0>
  <test4_1>2</test4_1>
  <test5_0>1</test5_0>
  <test5_1>2</test5_1>
  <conditions>1</conditions>
  <Person>
    <data>
      <lastname>Kowalski</lastname>
      <name>name1</name>
      <birthdate>18.10.1980</birthdate>
      <passport>1234556789</passport>
      <price>1889</price>
      <zipcode></zipcode>
      <city>TESTOWO1234</city>
      <street></street>
      <phone></phone>
      <email></email>
      <gender>H</gender>
    </data>
    <data>
      <lastname>Kowalski</lastname>
      <name>name2</name>
      <birthdate>18.10.1980</birthdate>
      <passport>1234556789</passport>
      <price>1889</price>
      <zipcode></zipcode>
      <city></city>
      <street></street>
      <phone></phone>
      <email></email>
      <gender>H</gender>
    </data>
  </Person>
  <Client>
    <lastname>Kowalski</lastname>
    <name>Jan</name>
    <street>ulicatestowa</street>
    <zipcode>12-345</zipcode>
    <city>testowe</city>
    <phone>123456</phone>
    <workphone>123456</workphone>
    <cellphone>123456</cellphone>
    <fax>123456</fax>
    <email>test@test.com</email>
    <data></data>
    <gender>Mr</gender>
    <country>Polska</country>
  </Client>
  <add_service>
    <data>
      <number>1</number>
      <allocation>
        <data>1</data>
        <data>2</data>
      </allocation>
      <fromDT>220211</fromDT>
      <toDT>020311</toDT>
      <type>V</type>
      <code>GNC_USLUGA</code >
      <accomodation></accomodation>
      <shift></shift>
      <len>8</len>
      <text>Gwarancja Niezmiennosci Ceny</text>
      <excludeIndex></excludeIndex>
    </data>
    <data>
      <number>2</number>
      <allocation>
        <data>1</data>
        <data>2</data>
      </allocation>
      <fromDT>220211</fromDT>
      <toDT>020311</toDT>
      <type>V</type>
      <code>UBSCOMP</code >
      <accomodation></accomodation>
      <shift></shift>
      <len>8</len>
      <text>Ubezp. Itaka Complex</text>
      <excludeIndex></excludeIndex>
    </data>
  </add_service>
</forminfo>

### Answer

Parameters:

| Element name | Attribute | Description |
| --- | --- | --- |
| booking_number | -  | Reservation number (only in case of successful reservation) |
| multifunction | -  | The content of the line multi-function - needed when you are booking from operators with own system SART |
| booking_info | -  | The information from the operator |
| booking_errors | -  | Errors in the process of booking (if any occured) |
| booking_error | -  | Description of error |
| booking_error | msg | Error message |
| result_message | msgType | Type of message |
| result_message | msgCode | Code currently.  <br>List of possible codes on our site: [http://www.merlinx.pl/mdsws/msgcode.csv](http://www.merlinx.pl/mdsws/msgcode.csv "http://www.merlinx.pl/mdsws/msgcode.csv") |
| offerstatus | status | Current status of the offer, possible options:  <br>BA – Available offer  <br>RQ - “on request” offer (only version 2.1)  <br>XX – offer not available  <br>TO - Timeout, or other problem with the connection (only version 2.1) |
| offerstatus | optionpossible | if the option booking is possible : 1 - yes, **empty** value, or 0 - no |
| offerstatus | optiondate | option till date (format: YYYY-MM-DD) |
| offerstatus | optiontime | option till hour (format: HH:MM) |
| merlin_offer_info | -  | Information contained in the price of services |
| info | -  | Information about the service contained in the offer |
| info | addedcost | If the value is given and equal to “1”, this service is an additional fee |
| info | hint | Information about the service from the organizer |
| pricetotal | price | Total price excursions |
| pricetotal | curr | Currency |

#### Example of a positive response

<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<response>
  <booking_number>5306211</booking_number>
  <booking_errors />
  <offerstatus optionpossible="" status="BA"/>
  <pricetotal price="3778" curr="PLN"/>
</response>

#### Example of a negative answer

<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<response>
  <booking_errors>
    <booking_error msg="example text"</booking_error>
 
  </booking_errors>
  <offerstatus optionpossible="" status="BA" />
  <pricetotal price="3778" curr="PLN"/>
</response>

#### An example of a negative response - an offer not available

  <?xml version="1.0" encoding="UTF-8" standalone="no" ?> 
<response>
  <result_message msgType="51" msgCode="927" /> 
  <offerstatus status="XX" /> 
  <pricetotal price="3118" curr="PLN" /> 
<hints cnt="1">
<hint>
<![CDATA[ UF_HOTEL_NOVACANCY
  ]]> 
  </hint>
  </hints>
<forminfo>
<extra_hotel>
  <type>hidden</type> 
  <value /> 
  </extra_hotel>
<InternalAction>
  <type>hidden</type> 
  <value>0</value> 
  </InternalAction>
<DelPersonIdx>
  <type>hidden</type> 
  <value>0</value> 
  </DelPersonIdx>
<ReservationMode>
  <type>hidden</type> 
  <value /> 
  </ReservationMode>
<check_price>
  <type>hidden</type> 
  <value>0</value> 
  </check_price>
<flaga>
  <type>hidden</type> 
  <value>1</value> 
  </flaga>
<unload_flag>
  <type>hidden</type> 
  <value>0</value> 
  </unload_flag>
<short_term>
  <type>hidden</type> 
  <value>0</value> 
  </short_term>
<additional_where_flag>
  <type>hidden</type> 
  <value /> 
  </additional_where_flag>
<load_orderby>
  <type>hidden</type> 
  <value>0</value> 
  </load_orderby>
<hideDefBirthdates>
  <type>hidden</type> 
  <value>1</value> 
  </hideDefBirthdates>
<new_search>
  <type>hidden</type> 
  <value /> 
  </new_search>
<check_payment_offer>
  <type>hidden</type> 
  <value>0</value> 
  </check_payment_offer>
<email_condition_checked>
  <type>hidden</type> 
  <value>0</value> 
  </email_condition_checked>
  </forminfo>
  </response>

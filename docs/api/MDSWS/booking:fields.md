# booking form - general rules

**WARNING:** When filling the form fields do not use local language characters. Local language characters must be converted to their ASCII equivalents - “ó” → “o” etc.

To simplify, only a selected fragment of returned form is explained, but same rules apply to the rest of responce.

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
    <passport>
      <type>text</type>
      <value/>
    </passport>
    <price>
      <type>text</type>
      <value>1889</value>
    </price>
    <zipcode>
      <type>text</type>
      <value/>
    </zipcode>
    <city>
      <type>text</type>
      <value/>
    </city>
    <street>
      <type>text</type>
      <value/>
    </street>
    <phone>
      <type>text</type>
      <value/>
    </phone>
    <email>
      <type>text</type>
      <value/>
    </email>
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
  </data>
  <data>(...)</data>
</Person>

Params:

| Element name | Description |
| --- | --- |
| Conditions, Persons, … | Form field name |
| type | Field type (coresponds to types available in HTML), possible values:  <br>checkbox, text, select, radio, hidden |
| checked | In case of field with type=checkbox means the field is checked by default. |
| value | Curent value |
| data | In case of arrays containing many elements (such as list of trip participants) is used to store subsequent array elements. |
| desc | List of descriptions for select field.  <br>Subsequent descriptions from **desc** are assigned to coresponding values from **values**, similar as in HTML:  <br><br><[option](http://december.com/html/4/element/option.html) value="(value from values)">(value from desc)</[option](http://december.com/html/4/element/option.html)> |
| values | List of values for field of type select |
| selected | Currently selected value for field of type select. |

When sending form, the values should be sent in abridged form, for example field:

<conditions>
  <type>checkbox</type>
  <checked>0</checked>
  <value>1</value>
</conditions>

is send as:

<conditions>1</conditions>

# booking form - field

## hints

text filed, containing information directly from Tour-operator, equivalent in MerlinX:  
![](/_media/booking:bookform-hints.png)

## forminfo

Params:

| Element name | Attribute | Description |
| --- | --- | --- |
| ReservationMode | -  | Type of booking, possible types:  <br>1 – normal booking  <br>0 – optional booking |
| conditions | -  | Has client accepted booking conditions:  <br>1 – yes  <br>0 - no |
| check_price | -  | If =1 allows for checking of price taking into account child age. |
| Person | -  | List of trip participants |
| Client | -  | Client personal data |
| add_service | -  | List of additional services |
| client_land | -  | Region of residence of the payer (required for some operators) |
| client_birthdate | -  | Date of birth of the payer (required for some operators) |
| ext_conditions | -  | Does the client accept additional information from the Tour-operator (included in the hints section)  <br>1 - Yes  <br>0 - No  <br>Equivalent in MerlinX:  <br>[![](/_media/booking:bookform-ext_conditions.png?w=300&tok=7636a9)](/_media/booking:bookform-ext_conditions.png "booking:bookform-ext_conditions.png") |
| marketing_condition | -  | Has customer consent to the processing of personal data for marketing purposes  <br>1 - Yes  <br>0 - No  <br>Equivalent in MerlinX:  <br>[![](/_media/booking:bookform-marketing_condition.png?w=300&tok=f9d689)](/_media/booking:bookform-marketing_condition.png "booking:bookform-marketing_condition.png") |
| skok_condition | -  | Has the customer has consent to the processing of personal data,  <br>1 - Yes  <br>0 - No  <br>Equivalent in MerlinX:  <br>[![](/_media/booking:bookform-skok_condition.png?w=300&tok=696285)](/_media/booking:bookform-skok_condition.png "booking:bookform-skok_condition.png") |

### person

contains information about particiants:  
![](/_media/booking:bookform-person.png)

| Field name | Description | Suggested format of data verification ( REGEX[1)](#fn__1) ) | Example value | Additional info |
| --- | --- | --- | --- | --- |
| lastname | Lastname | -  | Kowalski |     |
| name | Name | -  | Jan |     |
| birthdate | Birthdate | /^\d{2}\.\d{2}\.\d{4}$/ | 25.12.1989 | For children and infants age is required in ranges:  <br>- infants: up to 2 years (including)  <br>- children: 2-16 years (including) |
| passport | Passport | -  | 1234567 |     |
| zipcode | Zip code | /^[0-9]{2}-[0-9]{3}$/ | 12-345 |     |
| city | City | -  | Warszawa |     |
| street | Street | /^[\S]+?[^0-9]+? [0-9a-z]{1,3}(/[0-9]{1,2})?$/ | 3-go Maja 32/4 |     |
| phone | Phote | /^[\(\)\-\+/ 0-9]+$/ | (0-71) 123 456 |     |
| email | Email | /^[^ ]+@[^ ]+$/ | test@test.com |     |
| gender | Gender | -  | H   | Gender code corresponding to adults / children / infants may vary depending on the tour-operator. |

### client

payer, equivalent in MerlinX:  
![](/_media/booking:bookform-client.png)

| Field name | Description | Suggested format of data verification ( REGEX[2)](#fn__2) ) | Example value | Additional information |
| --- | --- | --- | --- | --- |
| lastname | Lastname | -  | Kowalski |     |
| name | Name | -  | Jan |     |
| street | Street | /^[\S]+?[^0-9]+? [0-9a-z]{1,3}(/[0-9]{1,2})?$/ | 3-go Maja 32/4 |     |
| zipcode | Zip code | /^[0-9]{2}-[0-9]{3}$/ | 12-345 |     |
| city | City | -  | Warszawa |     |
| phone | Phone | /^[\(\)\-\+/ 0-9]+$/ | (0-71) 123 456 |     |
| workphone | Work phone | /^[\(\)\-\+/ 0-9]+$/ | 0-71/ 123 456 |     |
| cellphone | Cell phone | /^[\(\)\-\+/ 0-9]+$/ | 0 601 602 603 |     |
| fax | Fax | /^[\(\)\-\+/ 0-9]+$/ | (0-71) 123 456 |     |
| email | Email | /^[^ ]+@[^ ]+$/ | test@test.com |     |
| paymenttype | Payment type | -  | 6   | The field used with the organizers of a group of “Triada” (In the rest of the organizers is not returned).  <br>Possible values ​​[3)](#fn__3):  <br>6 - Full payment 100%  <br>2 - Standard payment 40%  <br>8 - Advance “Zielona Karta 45%”  <br>  <br>Equivalent in MerlinX:  <br>[![](/_media/booking:bookform-paymenttype.png?w=300&tok=05ad06)](/_detail/booking:bookform-paymenttype.png?id=booking%3Afields "booking:bookform-paymenttype.png") |
| gender | Gender | -  | Pan |     |
| country | Country | -  | Polska |     |

### wishes

Client wishes.

<wishes>
  <type>select</type>
  <desc>
    <data>adjacent rooms pls</data>
    <data>baby cot pls</data>
    <data>close to</data>
    <data>first floor pls</data>
    <data>grandlit pls</data>
    <data>ground floor pls</data>
    <data>handicap.</data>
    <data>highest possible floor pls</data>
    <data>honeymoon</data>
    <data>not groundfloor pls</data>
    <data>quiet situated pls</data>
    <data>room with bath tube pls</data>
    <data>room with shower pls</data>
    <data>separated beds pls</data>
  </desc>
  <values>
    <data>adjacent rooms pls</data>
    <data>baby cot pls</data>
    <data>close to</data>
    <data>first floor pls</data>
    <data>grandlit pls</data>
    <data>ground floor pls</data>
    <data>handicap.</data>
    <data>highest possible floor pls</data>
    <data>honeymoon</data>
    <data>not groundfloor pls</data>
    <data>quiet situated pls</data>
    <data>room with bath tube pls</data>
    <data>room with shower pls</data>
    <data>separated beds pls</data>
  </values>
</wishes>

![](/_media/booking:bookform-wishes.png)

### payment

payment information:

*   Advance payment is the amount which should be regulated by the client / agent for the operator
    
*   The due date is automatically generated and you can not edit these fields.
    
*   In addition to the field of Amount of the reservation fee, you can edit the amount, but always above ammount which the system imposes
    

Automatically after a change, fields “sums up” the “rest” should be followed up and recalculated automatically. With offers of “LAST” and with a close date of departure, the field is not for editing and field reservation fee amounts to a total amount for the event.

Fields:

| Field name | Description |
| --- | --- |
| reservepay[0][0] | The amount of the reservation fee |
| reservepay[0][1] | Date of payment for the reservation fee |
| prepayment[0][0] | The amount of the advance rate |
| prepayment[0][1] | Date of payment to the rate of advance |
| prepayment[0][2] | The amount of rest |
| prepayment[0][3] | Date of payment for the rest |
| prepayment[0][4] | -  |

Example:

<reservepay>
  <data>
    <data>
      <type>text</type>
      <value>0</value>
    </data>
    <data>
      <type>text</type>
      <value>29-10-10</value>
    </data>
  </data>
</reservepay>
<prepayment>
  <data>
    <data>
      <type>text</type>
      <value>1000</value>
    </data>
    <data>
      <type>hidden</type>
      <value>29-10-2010</value>
    </data>
    <data>
      <type>hidden</type>
      <value>2650</value>
    </data>
    <data>
      <type>hidden</type>
      <value>14-12-2010</value>
    </data>
    <data>
      <type>hidden</type>
      <value>1</value>
    </data>
  </data>
</prepayment>

  
![](/_media/booking:bookform-payment.png)

### loyalty

Loyalty programs, equivalent in MerlinX:  
![](/_media/booking:bookform-loyalty.png)

### add_service

Additional services, equivalent in MerlinX:  
![](/_media/booking:bookform-add_service.png)

**WARNING:** Selecting a additional service can cause a change in the total price of the trip, and changing the amounts in payment section ([payment](/booking:fields#payment "booking:fields")).  
It is recommended to re-check the total price and rates of payment section, by sending action **check** with selected additional services.

Parameters:

| Field name | description |
| --- | --- |
| number | Number of selected service |
| allocation | An array assignment of passengers to the service.  <br>Subsequent array indices should include membership number that is assigned to the service.  <br>Participant number corresponds to its position in the list of participants (array **Person**).  <br>  <br>If the fields in allocation are of type **hidden**, this means that for a given service is not possible to assign the individual tour participants for selected service. |
| fromDT | Date from which to begin service |
| toDT | Date to which the service will last |
| type | Type of Service on MerlinX booking mask |
| code | Code of Service on MerlinX booking mask |
| accomodation |     |
| shift | - (always empty) |
| len | Length of service in days (the difference between **fromDT** - **toDT**) |
| text | Description of service |
| excludeIndex | Code List (from field **code**), separated by a comma, or services that can not be booked at the same time to the service |
| amount | Amount of payment |

Przykład:

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
    <code>GNC_USLUGA< /code>
    <accomodation></accomodation>
    <shift></shift>
    <len>8</len>
    <text>Gwarancja Niezmiennosci Ceny</text>
    <excludeIndex></excludeIndex>
  </data>
</add_service>

[1)](#fnt__1) , [2)](#fnt__2)

[http://pl2.php.net/manual/pl/reference.pcre.pattern.syntax.php](http://pl2.php.net/manual/pl/reference.pcre.pattern.syntax.php "http://pl2.php.net/manual/pl/reference.pcre.pattern.syntax.php")

[3)](#fnt__3)

NOTE: These values ​​may change. Current list is always returned in the information from the organizer - field “Hints”

# **MDSWS V4**

## Main change from version 3 :

##### Reduced response times!

##### url address

[regions](/data:request:regions "data:request:regions") [details](/data:request:details "data:request:details") [filters](/data:request:filters "data:request:filters") [groups](/data:request:groups "data:request:groups") [Multirequest](/data:multirequest "data:multirequest") [skiregions](/data:request:skiregions "data:request:skiregions") [check_external_flight_nowait](/externaldat:check_external_flight_nowait "externaldat:check_external_flight_nowait") [check_external_hotel_wait](/externaldat:check_external_hotel_wait "externaldat:check_external_hotel_wait") [check_external_hotel_nowait](/externaldat:check_external_hotel_nowait "externaldat:check_external_hotel_nowait") [check_external_flight_wait](/externaldat:check_external_flight_wait "externaldat:check_external_flight_wait")

[http://mws.merlinx.pl/dataV4/](http://mws.merlinx.pl/dataV4/ "http://mws.merlinx.pl/dataV4/")

##### different auth node in request :

<source></source> <srcDomain></srcDomain>

for exmaple :

```xml
 <auth>
    <login>login</login>
    <pass>password</pass>
    <source>B2C</source>
    <srcDomain>test.mydomain.pl</srcDomain>
  </auth>
```

##### dynamic packages:

mandatory fields in dynamic packaging:

paxes, <ofr_xStatus>OK,RQ</ofr_xStatus> , [ofr_tourOp](/data:fields:names:ofr_tourop "data:fields:names:ofr_tourop") , [trp_depDate](/data:fields:names:trp_depdate "data:fields:names:trp_depdate")

example reqest:

```xml
<?xml version="1.0"?>
<mds>
  <auth>
    <login>login</login>
    <pass>password</pass>
    <source>B2C</source>
    <srcDomain>test.mydomain.pl</srcDomain>
  </auth>
  <request>
    <type>groups</type>
    <conditions>
      <ofr_tourOp>XSTE</ofr_tourOp>
      <limit_count>1000</limit_count>
      <limit_from>0</limit_from>
      <group_by>tourOpAndCode</group_by>
      <ofr_xStatus>OK,RQ</ofr_xStatus>
      <order_by>obj_xName</order_by>
      <trp_destination>26_4100</trp_destination>
      <trp_depDate>20160617:20160930</trp_depDate>
      <par_adt>2</par_adt>
    </conditions>
  </request>
</mds>
```

##### dynamic packages and resabee booking

Following actions are used to book

[WebService address]/bookV4/

Example address:

[http://mws.merlinx.pl/bookV4/](http://mws.merlinx.pl/bookV4/ "http://mws.merlinx.pl/bookV4/")

List of possible action types

| Action (link to detailed description) | Description |
| --- | --- |
| [checkavail](/bookingv4:checkavail "bookingv4:checkavail") | availability check simplified |
| [check](/bookingv4:check "bookingv4:check") | availability check |
| [book](/bookingv4:book "bookingv4:book") | booking |
| [bookingstatus](/bookingv4:bookingstatus "bookingv4:bookingstatus") | bookingstatus |
| [bookingchangecheck](/bookingv4:bookingchangecheck "bookingv4:bookingchangecheck") | check change of booking possibility |
| [bookingchange](/bookingv4:bookingchange "bookingv4:bookingchange") | change of booking |
| [bookingcancelcheck](/bookingv4:bookingcancelcheck "bookingv4:bookingcancelcheck") | check cancel of booking possibility |
| [bookingcancel](/bookingv4:bookingcancel "bookingv4:bookingcancel") | cancel of booking |
| [vouchers](/bookingv4:vouchers "bookingv4:vouchers") | vouchers |

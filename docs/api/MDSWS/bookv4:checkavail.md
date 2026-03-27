## POST /bookv4/checkavail

A simplified book availability check

### Request

Type:
| Element name | Description |
| --- | --- |
| checkavail | check if a booking can be made with given data |

Conditions:

| Element name | Description |
| --- | --- |
| ofr_tourOp | offer tourop |
| ofr_id | offer id |
| par_adt | number of adults |
| par_chd | number of childs |
| par_inf | number of infants |
| currency | currency |
| language | language |
| extraHotel | Selected extrahotel. Required for 2in1 or Z+W offers. Format:  <br><extraHotel>  <br><htlCode>1000</htlCode>  <br><htlRoomCode>DBL</htlRoomCode>  <br><htlSrvCode>H</htlSrvCode>  <br><fromDate>2020-10-27</fromDate>  <br><toDate>2020-10-30</toDate>  <br></extraHotel> |

<?xml version='1.0'?>
<mds>
  <auth>
    <source>MDSWS</source>
    <srcDomain>test.domain.pl</srcDomain>
    <login>login</login>
    <pass>password</pass>
  </auth>
  <request>
    <type>checkavail</type>
    <conditions>
      <par_adt>2</par_adt>
      <ofr_id>9160229c7e28e2ba2180fba6b9cd66cb65203760ac79fd7ad8338ac7cdbf2516</ofr_id>
      <ofr_tourOp>VITX</ofr_tourOp>
    </conditions>
       <forminfo>
      <persons>
        <person int="0">
          <gender>H</gender>
          <lastname>AAAA</lastname>
          <firstname>AAAA</firstname>
          <birthdate>09.11.1986</birthdate>
        </person>
        <person int="1">
          <gender>H</gender>
          <lastname>AAAA</lastname>
          <firstname>AAAA</firstname>
          <birthdate>09.11.1986</birthdate>
        </person>
      </persons>
    </forminfo>
  </request>
</mds>

### Answer

<?xml version="1.0" encoding="UTF-8" standalone="no"?>
 <response>
 <merlin_offer_info></merlin_offer_info>
 <merlin_offer_status>OK</merlin_offer_status>
 <result_message msgCode="712" subCode="999000226" />
 <flightOutFP>4</flightOutFP>
 <flightRetFP>4</flightRetFP>
 <booking_info></booking_info>
 <pricetotal price="500" curr="PLN" />
 <persons>
  <person id="0" gender="H" date="010101" price="500"/><
 </persons>
</response>

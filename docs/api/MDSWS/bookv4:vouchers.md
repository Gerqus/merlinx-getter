### (POST?) /bookv4/vouchers

Request for getting list of documents for the booking.

### Request

Type:
| Element name | Description |
| --- | --- |
| vouchers | get list of vouchers for a booking |

Conditions:
| Element name | Description |
| --- | --- |
| language | Language code |
| currency | Currency code |
| ofr_tourOp | Touroperator code |
| expedient_code | Expedient code |
| booking_number | number of a booking |

```xml
<?xml version="1.0" encoding="UTF-8"?>
<mds>
  <auth>
    <login>login</login>
    <pass>password<pass>
    <source>MDSWS</source>
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
    <type>vouchers</type>
  </request>
</mds>
```

### Answer

```xml
<response>
    <status>OK</status>
    <vouchers>
        <voucher>
            <id>0</id>
            <name>Umowa/zgłoszenie</name>
            <link>
                http://192.168.99.110:12345/linkV1/?h=109x74e6c7922995f29ee34d34e60e2484576c407471c7063ee4775c7f3fb8618480eb2e2d729df6746944ddf69150c12ed3680ecb864d50ac580faebbe7529e3de35cac5ada6cef5c79f7dcc7b1c28b31d1eb70e9d68a6e334613de76c1c43b272b750acb8238f4edb60a84124c5a
            </link>
        </voucher>
        <voucher>
            <id>1</id>
            <name>Potwierdzenie dla agenta</name>
            <link>
                http://192.168.99.110:12345/linkV1/?h=129x74e6c7922995f29ee34d34e60e2484576c407471c7063ee4775c7f3aba7a8488327343787a63725d9fce4d79a511d03f36bae7bfe1b4a8a6dab64de1dd4d827968795621b5ed17b84a7c307fc3a03b2f24ddc7b8c69087d93ba49438a5cf9994cc576ce7c4953d0fdd8c6fd850456c1ad883e87e26778b710d1c15d10eb381901a
            </link>
        </voucher>
        <voucher>
            <id>2</id>
            <name>Potwierdzenie dla klienta</name>
            <link>
                http://192.168.99.110:12345/linkV1/?h=130x74e6c7922995f29ee34d34e60e2484576c407471c7063ee4775c7f3aba7a8488327343787a63725d9fce4d79a511d03f36bae7bfe1b4a8a6dab64de1dd4d827968795621b5ed17b84a7c307fc3a03b2f24ddc7b8c69087d93ba49438a5cf9994cc576ce7c4953d0fdd8c6fd850456c18f7da7038488766851428b98e3db78eea1257
            </link>
        </voucher>
    </vouchers>
</response>
```

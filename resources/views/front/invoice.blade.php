<html>
<head>
<title>Invoice</title>
<style type="text/css">

  table { font-family: Helvetica, Arial, Verdana; border-collapse: collapse; }
  table td, table thead { padding: 0; margin: 0; border-spacing: 0px; }
  th{
    padding: 2px;
    text-align: center;
  }
  @media print {
    thead { display: table-header-group; }
  }

</style>
</head>
<table style="width: 100%; border-spacing: 0px; border-collapse: collapse;">
  <thead>
    <tr>
      <th colspan="8">
        <div>
          <div style="display: flex; flex-direction: column; text-align: center;">
            <label style="font-size: 18px; margin-bottom: 0px;">HOME U(M) SDN BHD (125272-P)</label>
            <span style="font-size: 11px;">{!! nl2br(e($branch_address)) !!}</span>
            <span style="font-size: 11px;">Tel: {{ $contact_number }}</span>

            <label style="font-size: 18px; margin-top: 10px;">INVOICE</label>
          </div>

          <div style="width: 100%; display: inline-block; border: 1px solid #ccc;">
            <div style="width: 300px; float: right; display: flex; text-align: left;">
              <div style="flex: 1; padding: 5px; display: flex; flex-direction: column;">
                <span style="font-size: 14px;">Invoice No</span>
                <span style="font-weight: normal; font-size: 14px;">Date</span>
                <span style="font-weight: normal; font-size: 14px;">Reference</span>
              </div>

              <div style="flex: 1; padding: 5px; display: flex; flex-direction: column;">
                <span style="font-size: 14px;">:</span>
                <span style="font-weight: normal; font-size: 14px;">:</span>
                <span style="font-weight: normal; font-size: 14px;">:</span>
              </div>

              <div style="flex: 1; background: #fff; padding: 5px; display: flex; flex-direction: column;text-align: right;">
                <span style="font-size: 14px;">{{ $transaction->transaction_no }}</span>
                <span style="font-weight: normal; font-size: 14px;">{{ date('d-M-Y', strtotime($transaction->transaction_date)) }}</span>
                <span style="font-weight: normal; font-size: 14px;">{{ $transaction->reference_no }}</span>
              </div>
            </div>
          </div>
        </div>
      </th>
    </tr>
    <tr>
      <th colspan="8">
        <div style="width: 100%; height: 5px;"></div>
      </th>
    </tr>
    <tr style="text-align: left; font-size:14px;">
      <th style="background: #ccc; border: 1px solid #aaa; border-top: 2px solid #333; border-bottom: 1px solid #333;border-left: 1px solid black;">
        <span style="font-size: 16px; padding-left: 5px;">No</span>
      </th>
      <th style="background: #ccc; border: 1px solid #aaa; border-top: 2px solid #333; border-bottom: 1px solid #333;">
        <span style="font-size: 16px; padding-left: 5px;">Barcode</span>
      </th>
      <th style="background: #ccc; border: 1px solid #aaa; border-top: 2px solid #333; border-bottom: 1px solid #333;">
        <span style="font-size: 16px; padding-left: 5px;">Product</span>
      </th>
      <th style="background: #ccc; border: 1px solid #aaa; border-top: 2px solid #333; border-bottom: 1px solid #333;">
        <span style="font-size: 16px; padding-left: 5px;">Qty</span>
      </th>
      <th style="background: #ccc; border: 1px solid #aaa; border-top: 2px solid #333; border-bottom: 1px solid #333;">
        <span style="font-size: 16px; padding-left: 5px;">Unit Price</span>
      </th>
      <th style="background: #ccc; border: 1px solid #aaa; border-top: 2px solid #333; border-bottom: 1px solid #333;">
        <span style="font-size: 16px; padding-left: 5px;">Discount</span>
      </th>
      <th style="background: #ccc; border: 1px solid #aaa; border-top: 2px solid #333; border-bottom: 1px solid #333;">
        <span style="font-size: 16px; padding-left: 5px;">Tax</span>
      </th>
      <th style="background: #ccc; border: 1px solid #aaa; border-top: 2px solid #333; border-bottom: 1px solid #333; border-right: 2px solid #333;">
        <span style="font-size: 16px; padding-left: 5px;">Amount</span>
      </th>
    </tr>
  </thead>
  <tbody>
    @foreach($transaction_detail_list as $key => $detail)
      <tr>
        <td style="border-right: 1px solid #aaa; border-left: 1px solid black; padding: 0 5px; text-align: right;">{{ $key + 1 }}</td>
        <td style="border-right: 1px solid #aaa; padding: 0 5px;">{{ $detail->barcode }}</td>
        <td style="border-right: 1px solid #aaa; padding: 0 5px;">{{ $detail->product_name }}</td>
        <td style="border-right: 1px solid #aaa; padding: 0 5px; text-align: right;">{{ $detail->quantity * $detail->measurement }}</td>
        <td style="border-right: 1px solid #aaa; padding: 0 5px; text-align: right;">{{ number_format($detail->price, 2) }}</td>
        <td style="border-right: 1px solid #aaa; padding: 0 5px; text-align: right;">0.00</td>
        <td style="border-right: 1px solid #aaa; padding: 0 5px; text-align: right;">0.00</td>
        <td style="border-right: 2px solid #333; padding: 0 5px; text-align: right;">{{ number_format($detail->total, 2) }}</td>
      </tr>
    @endforeach

    <tr style="font-size:14px;">
      <td colspan="5" rowspan="5" style="padding: 0 5px; border: 1px solid #aaa; border-right-width: 1px; border-bottom-color: #333; border-bottom-width: 2px;border-left: 1px solid black;">
        <div>E. & O.E.</div>
        <div>No complaint will be considered unless notified within 10 days of this debit note date.</div>
        <div>All cheque should be crossed A/C payee only.</div>
      </td>
      <td colspan="2" style="padding: 0 5px; border-top: 1px solid #aaa; border-right: 1px solid #aaa; border-bottom-width: 0px;"><b>Sub Total</b></td>
      <td style="padding: 0 5px; text-align: right; border-right: 2px solid #333; border-top: 1px solid #aaa;">{{ $transaction->subtotal }}</td>
    </tr>

    <tr style="font-size:14px;">
      <td colspan="2" style="padding: 0 5px; border-right: 1px solid #aaa; border-bottom-width: 0px;">Item Discount</td>
      <td style="padding: 0 5px; text-align: right; border-right: 2px solid #333;">{{ number_format($transaction->total_discount, 2) }}</td>
    </tr>

    <tr style="font-size:14px;">
      <td colspan="2" style="padding: 0 5px; border-right: 1px solid #aaa; border-bottom-width: 0px;">Tax Total (6%)</td>
      <td style="padding: 0 5px; text-align: right; border-right: 2px solid #333;">0.00</td>
    </tr>

    <tr style="font-size:14px;">
      <td colspan="2" style="padding: 0 5px; border-right: 1px solid #aaa; border-bottom-width: 0px;"></td>
      <td style="padding: 0 5px; text-align: right; border-right: 2px solid #333;"></td>
    </tr>

    <tr style="font-size:14px;">
      <td colspan="2" style="padding: 0 5px; border-right: 1px solid #aaa; border-bottom: 2px solid #333;">Net Total <span style="float: right;">(MYR)</span></td>
      <td style="padding: 0 5px; border-bottom: 2px solid #333; border-right: 2px solid #333; text-align: right;">{{ number_format($transaction->total, 2) }}</td>
    </tr>

    <tr>
      <td colspan="8">
        <div style="float: right;margin-top: 5px;font-size:14px;">For HOME U (M) SDN.BHD.</div>
      </td>
    </tr>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="8">
        <div style="margin-top: 50px;">
          <div style="border-bottom: 1px solid #333; width: 300px;"></div>
          <span>Company's Chop & Authorised Signature</span>
        </div>
      </td>
    </tr>
  </tfoot>
</table>

<script>
  
  window.print();

</script>

</html>
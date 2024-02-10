<script setup>

const printInvoice = () => {
      // Open print dialog to print the invoice
      window.print();
    }
</script>


<template>

<div class="offset-xl-2 col-xl-8 col-lg-12 col-md-12 col-sm-12 col-12 padding" id="printable">
<div class="">
    <hr style="border-color: red;border-top-width: 5px; margin-bottom:{{ $spacer }}; ">

    <table style="width: 100%">
        <tr>
            <td style="width: 60%">
                <table style="width: 100%">
                    <tr>
                        <td style="width: 30%;text-align: center;padding-right: 40px;">
                            <img src="{{ asset('asset/img/logo.jpeg') }}" class="" alt="User Image"
                                style="width: 100%; height: 100px">
                        </td>
                        <td style="width: 49%">
                            <h3>Top Man</h3>
                            <p>Phone: 0162363636</p>
                            <span>Address: Saifur Rahman Road, Moulvibazar, Moulvibazar Sadar. Email:
                                topman.bd@gmail.com</span>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 40%; text-align:right">
                <span>Invoice No:</span><br>
                <span>1</span><br>
                <span>Date:</span><br>
                <span>date</span><br>
            </td>
        </tr>
    </table>

    
    <div class="card-body pt-0">
        <div class="row">
            <div class="col-sm-6">
               
            </div>
        </div>

        <p style="margin: 0"><strong>Products:</strong></p>
        <div class="productTable" style="border: 2px dotted #111; border-radius: 5px;">
            <div class="table-responsive-sm">

                    <div class="mt-2">
                        <table class="table table-sm text-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>

                                    <tr>
                                        <!-- <td><small>{{ $loop->iteration }}</small></td>
                                        <td><small>{{ $product->product->name }}</small></td>
                                        <td><small>{{ $product->price }}</small></td>
                                        <td><small>{{ $product->quantity }}</small></td>
                                        <td>
                                            <small>{{ $product->quantity * $product->price }}</small>
                                        </td> -->

                                    </tr>

                            </tbody>
                        </table>
                    </div>
                
            </div>
        </div>

        <div class="col-lg-6 col-sm-5 ml-auto mt-3 table-responsive-sm" id="paymentTable"
            style="border: 2px dotted #111; border-radius: 5px">
            <table class="table table-sm text-center">
                <thead>
                    <tr>
                       
                    </tr>
                </thead>

                <tbody>

                    <tr>
                       
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Print button -->
<button class="btn btn-primary ml-2" @click="printInvoice">Print Invoice</button>

</div>


</template>
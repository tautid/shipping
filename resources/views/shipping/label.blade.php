<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Label - {{ $shipping->trx_id }}</title>
    <style>
        /* Base Font Family */
        html,
        body {
            font-family: var(--default-font-family, ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji");
        }

        @media print {
            @page {
                size: 100mm 100mm;
                margin: 0;
            }

            html,
            body {
                width: 100mm;
                height: 100mm;
                padding: 0;
                margin: 0;
                font-size: 8px;
                line-height: 1.1;
                font-family: var(--default-font-family, ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji");
            }

            #print-section {
                width: 100mm;
                height: 100mm;
                box-sizing: border-box;
                padding: 2mm 2mm 2mm 0mm;
            }

            img {
                max-width: 100%;
                height: auto;
                filter: grayscale(100%) !important;
            }
        }

        /* Layout Classes */
        .sheet {
            display: block;
        }

        .padding-custom {
            padding: 2mm 2mm 2mm 0mm;
        }

        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }

        .flex {
            display: flex;
        }

        .flex-col {
            flex-direction: column;
        }

        .flex-row {
            flex-direction: row;
        }

        .justify-between {
            justify-content: space-between;
        }

        .justify-center {
            justify-content: center;
        }

        .items-center {
            align-items: center;
        }

        .items-end {
            align-items: flex-end;
        }

        .space-x-2 > * + * {
            margin-left: 0.5rem;
        }

        .w-1-2 {
            width: 50%;
        }

        .w-40 {
            width: 10rem;
        }

        .max-w-12rem {
            max-width: 12rem;
        }

        .h-16 {
            height: 4rem;
        }

        /* Text Classes */
        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-sm {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }

        .text-base {
            font-size: 1rem;
            line-height: 1.5rem;
        }

        .text-lg {
            font-size: 1.125rem;
            line-height: 1.75rem;
        }

        .text-xl {
            font-size: 1.25rem;
            line-height: 1.75rem;
        }

        .text-2xl {
            font-size: 1.5rem;
            line-height: 2rem;
        }

        .text-4xl {
            font-size: 2.25rem;
            line-height: 2.5rem;
        }

        .text-6xl {
            font-size: 3.75rem;
            line-height: 1;
        }

        .leading-tight {
            line-height: 1.25;
        }

        .font-bold {
            font-weight: 700;
        }

        .font-extrabold {
            font-weight: 800;
        }

        .font-black {
            font-weight: 900;
        }

        .italic {
            font-style: italic;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .tracking-wider {
            letter-spacing: 0.05em;
        }

        /* Color Classes */
        .text-white {
            color: #ffffff;
        }

        .text-gray-700 {
            color: #374151;
        }

        .bg-black {
            background-color: #000000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Spacing Classes */
        .px-2 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .py-1 {
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }

        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .mt-1 {
            margin-top: 0.25rem;
        }

        .mt-2 {
            margin-top: 0.5rem;
        }

        .mb-2 {
            margin-bottom: 0.5rem;
        }

        /* Border Classes */
        .border-b {
            border-bottom-width: 1px;
            border-bottom-style: solid;
            border-bottom-color: #000000;
        }

        .border-t {
            border-top-width: 1px;
            border-top-style: solid;
            border-top-color: #000000;
        }

        .border-y {
            border-top-width: 1px;
            border-bottom-width: 1px;
            border-top-style: solid;
            border-bottom-style: solid;
            border-top-color: #000000;
            border-bottom-color: #000000;
        }

        .border-2 {
            border-width: 2px;
        }

        .border-solid {
            border-style: solid;
        }

        .border-black {
            border-color: #000000;
        }

        .rounded-sm {
            border-radius: 0.125rem;
        }

        /* Display Classes */
        .inline-block {
            display: inline-block;
        }
    </style>
</head>
<body>

<section id="print-section" class="sheet padding-custom mx-auto text-xl leading-tight">
    {{-- SHIPPING HEADER --}}
    <div class="text-left flex justify-between items-end border-b">
        <div>
            <div class="text-4xl font-bold">{{ config('taut-shipping.brand_name') }}</div>
        </div>
        <div class="text-4xl font-black uppercase">
            {{ strtolower($shipping->method_service) }}
        </div>
    </div>

    {{-- Channel Logo --}}
    @php
        $channelImageUrl = \TautId\Shipping\Factories\ShippingMethodDriverFactory::getDriver($shipping->method_driver)->channelImageUrl($shipping->method_channel);
    @endphp

    {{-- Logo and Service Info --}}
    <div class="flex justify-between items-center">
        @if($channelImageUrl)
            <img src="{{ $channelImageUrl }}" alt="{{ $shipping->method_channel }}" class="h-16 max-w-12rem">
        @else
            <div class="flex flex-col items-center mb-2 text-4xl font-bold">
                {{ $shipping->method_name }}
            </div>
        @endif
        {{-- TRACKING NUMBER BARCODE --}}
        @if($shipping->awb)
            <div class="flex flex-col items-center mb-2">
                <div class="text-sm font-bold mt-1">
                    Tracking Number
                </div>
                    @php
                        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                        echo '<img style="width: 100%; height: 25px;" src="data:image/png;base64,' .
                            base64_encode($generator->getBarcode($shipping->awb, $generator::TYPE_CODE_128)) .
                            '">';
                    @endphp
                    <h4 class="font-bold flex text-sm tracking-wider">
                        {{ mb_strtoupper($shipping->awb) }}
                    </h4>
            </div>
        @endif
    </div>

    {{-- Service Details --}}
    <div class="text-xl flex justify-between items-center">
        <div class="flex flex-col">
            <span class="font-extrabold">Weight: {{ number_format(($shipping->package_weight ?? 1) / 1000, 2) }}kg</span>
        </div>
        <span class="font-extrabold px-2 py-1 bg-black text-white rounded-sm">
            DESTINATION: {{ $shipping->destination->city . ' (' . strtoupper($shipping->destination->postal_code) . ')' }}
        </span>
    </div>

    {{-- if COD_FLAG is YES --}}
    @if ($shipping->is_cod)
        <div class="border-2 border-solid border-black px-2 py-1 mt-2 text-6xl font-bold rounded-sm text-center">
            COD: {{ number_format($shipping->package_price + $shipping->shipping_cost, 0) }}
        </div>
    @endif

    {{-- Instruction Notice --}}
    <div class="border-2 border-solid bg-black text-white px-2 py-1 mt-2 text-base italic font-bold rounded-sm text-center">
        Please confirm to Sender before returning package with unboxing video.
    </div>

    <div class="flex space-x-2 text-xl">
        {{-- Sender --}}
        <div class="py-2 w-1-2">
            From:<br>
            <span class="font-bold">{{ $shipping->origin->name }}</span><br>
            <span class="font-bold">{{ $shipping->origin->phone }}</span><br>
            <span class="text-base">{!! nl2br(e($shipping->origin->address . ', ' . $shipping->origin->subdistrict . ', ' . $shipping->origin->district . ', ' . $shipping->origin->city . ', ' . $shipping->origin->province . ' (' . $shipping->origin->postal_code . ')')) !!}</span>
        </div>

        {{-- Recipient --}}
        <div class="py-2 w-1-2">
            To:<br>
            <span class="font-bold">{{ $shipping->destination->name }}</span><br>
            <span class="font-bold">{{ $shipping->destination->phone }}</span><br>
            <span class="text-base">{!! nl2br(e($shipping->destination->address . ', ' . $shipping->destination->subdistrict . ', ' . $shipping->destination->district . ', ' . $shipping->destination->city . ', ' . $shipping->destination->province . ' (' . $shipping->destination->postal_code . ')')) !!}</span>
        </div>
    </div>

    {{-- Package Information --}}
    <div class="py-1 border-t text-xl">
        <div class="flex justify-between items-center">
            <div class="flex flex-col">
                <span class="font-bold">PACKAGE DIMENSIONS:</span>
                <span>{{ $shipping->dimension->length ?? 0 }} × {{ $shipping->dimension->width ?? 0 }} × {{ $shipping->dimension->height ?? 0 }} cm</span>
            </div>
            <div class="flex flex-col text-right">
                <span class="font-bold">SHIPPING DATE:</span>
                <span>{{ $shipping->date->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    <div class="py-1 border-y text-xl">
        INSTRUCTIONS:
        <span class="font-bold">{{ $shipping->note ?? '-' }}</span>
    </div>

    {{-- Order ID --}}
    <div class="flex flex-row mt-2 mb-2 justify-between">
        <div class="text-2xl font-bold mt-1" style="width:100px;">
            Order ID
        </div>
        <div>
            @php
                $orderId = $shipping->trx_id;
                $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                echo '<img style="width: 100%; height: 30px;" src="data:image/png;base64,' .
                    base64_encode($generator->getBarcode($orderId, $generator::TYPE_CODE_128)) .
                    '">';
            @endphp
            <h4 class="font-bold flex justify-center text-sm tracking-wider">
                {{ mb_strtoupper($orderId) }}
            </h4>
        </div>
    </div>
</section>

<script>
    window.addEventListener("load", function() {
        window.print();
    });

    window.addEventListener("afterprint", function() {
        window.close();
    });
</script>
</body>
</html>

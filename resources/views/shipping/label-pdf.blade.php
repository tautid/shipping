<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Label - {{ $shipping->trx_id }}</title>
    <style>
        /* Base Font Family - DomPDF compatible */
        html,
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
        }

        /* Page setup for PDF */
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
            font-size: 10px;
            line-height: 1.1;
            font-family: "DejaVu Sans", Arial, sans-serif;
        }

        #print-section {
            width: 100mm;
            height: auto;
            box-sizing: border-box;
            padding: 0;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        /* Layout Classes - DomPDF compatible */
        .sheet {
            display: block;
        }

        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }

        /* Flexbox alternatives for DomPDF */
        .flex {
            display: table;
            width: 100%;
        }

        .flex-col {
            display: block;
        }

        .flex-row {
            display: table;
            width: 100%;
        }

        .justify-between {
            display: table;
            width: 100%;
        }

        .justify-between > * {
            display: table-cell;
            vertical-align: top;
        }

        .justify-between > *:last-child {
            text-align: right;
        }

        .justify-center {
            text-align: center;
        }

        .items-center {
            vertical-align: middle;
        }

        .items-end {
            vertical-align: bottom;
        }

        .space-x-2 {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .space-x-2 > * {
            display: table-cell;
            padding-right: 0.5rem;
        }

        .space-x-2 > *:last-child {
            padding-right: 0;
        }

        .w-1-2 {
            width: 50%;
            display: table-cell;
        }

        .w-40 {
            width: 10rem;
        }

        .max-w-12rem {
            max-width: 12rem;
        }

        .h-16 {
            height: 2.0rem;
        }

        /* Text Classes */
        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-sm {
            font-size: 0.9rem;
            line-height: 0.95rem;
        }

        .text-base {
            font-size: 1.0rem;
            line-height: 1.05rem;
        }

        .text-lg {
            font-size: 1.1rem;
            line-height: 1.15rem;
        }

        .text-xl {
            font-size: 1.3rem;
            line-height: 1.35rem;
        }

        .text-2xl {
            font-size: 1.6rem;
            line-height: 1.65rem;
        }

        .text-4xl {
            font-size: 2.2rem;
            line-height: 2.25rem;
        }

        .text-6xl {
            font-size: 3.0rem;
            line-height: 3.05rem;
        }

        .leading-tight {
            line-height: 1.1;
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
            background-color: #000000 !important;
            background: #000000 !important;
            color: #ffffff !important;
        }

        /* Force background colors for PDF */
        .pdf-bg-black {
            background-color: #000000 !important;
            background: #000000 !important;
            color: #ffffff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        /* Spacing Classes - Optimized for 100mm fit */
        .px-2 {
            padding-left: 0.2rem;
            padding-right: 0.2rem;
        }

        .py-1 {
            padding-top: 0.1rem;
            padding-bottom: 0.1rem;
        }

        .py-2 {
            padding-top: 0.15rem;
            padding-bottom: 0.15rem;
        }

        .mt-1 {
            margin-top: 0.1rem;
        }

        .mt-2 {
            margin-top: 0.15rem;
        }

        .mb-2 {
            margin-bottom: 0.1rem;
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

        /* Compact spacing for 100mm fit */
        .compact-section {
            margin-top: 0.05rem;
            margin-bottom: 0.05rem;
        }

        .compact-text {
            line-height: 1.0;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>

<section id="print-section" class="sheet mx-auto text-xl leading-tight">
    {{-- SHIPPING HEADER --}}
    <div class="text-left justify-between items-end border-b">
        <div style="display: table-cell; vertical-align: bottom;">
            <div class="text-4xl font-bold">{{ config('taut-shipping.brand_name') }}</div>
        </div>
        <div class="text-4xl font-black uppercase" style="display: table-cell; vertical-align: bottom; text-align: right;">
            {{ strtolower($shipping->method_service) }}
        </div>
    </div>

    {{-- Channel Logo --}}
    @php
        $channelImageUrl = \TautId\Shipping\Factories\ShippingMethodDriverFactory::getDriver($shipping->method_driver)->channelImageUrl($shipping->method_channel, true);
    @endphp

    {{-- Logo and Service Info --}}
    <div class="justify-between items-center">
        <div style="display: table-cell; vertical-align: middle; width: 50%;">
            @if($channelImageUrl)
                {{-- Base64 grayscale image for PDF compatibility --}}
                <img src="{{ $channelImageUrl }}" alt="{{ $shipping->method_channel }}" class="h-16 max-w-12rem" style="display: block;">
            @else
                <div class="text-center mb-2 text-4xl font-bold">
                    {{ $shipping->method_name }}
                </div>
            @endif
        </div>
        {{-- TRACKING NUMBER BARCODE --}}
        @if($shipping->awb)
            <div style="display: table-cell; vertical-align: middle; text-align: center; width: 50%;">
                <div class="text-sm font-bold mt-1">
                    Tracking Number
                </div>
                    @php
                        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                        echo '<img style="width: 100%; height: 25px;" src="data:image/png;base64,' .
                            base64_encode($generator->getBarcode($shipping->awb, $generator::TYPE_CODE_128)) .
                            '">';
                    @endphp
                    <h4 class="font-bold text-sm tracking-wider">
                        {{ mb_strtoupper($shipping->awb) }}
                    </h4>
            </div>
        @endif
    </div>

    {{-- Service Details --}}
    <div class="text-xl justify-between items-center compact-section">
        <div style="display: table-cell; vertical-align: middle;">
            <span class="font-extrabold">Weight: {{ number_format(($shipping->package_weight ?? 1) / 1000, 2) }}kg</span>
        </div>
        <div style="display: table-cell; vertical-align: middle; text-align: right;">
            <span class="font-extrabold px-2 py-1 bg-black text-white rounded-sm">
                DESTINATION: {{ $shipping->destination->city . ' (' . strtoupper($shipping->destination->postal_code) . ')' }}
            </span>
        </div>
    </div>

    {{-- if COD_FLAG is YES --}}
    @if ($shipping->is_cod)
        <div class="border-2 border-solid border-black px-2 py-1 mt-1 text-4xl font-bold rounded-sm text-center compact-section">
            COD: {{ number_format($shipping->package_price + $shipping->shipping_cost, 0) }}
        </div>
    @endif

    {{-- Instruction Notice --}}
    <div class="border-2 border-solid bg-black text-white px-2 py-1 mt-1 text-base italic font-bold rounded-sm text-center compact-section">
        Please confirm to Sender before returning package with unboxing video.
    </div>

        <div class="space-x-2 text-base compact-section">
        {{-- Sender --}}
        <div class="py-1 w-1-2">
            <span class="text-sm">From:</span><br>
            <span class="font-bold text-base">{{ $shipping->origin->name }}</span><br>
            <span class="font-bold text-sm">{{ $shipping->origin->phone }}</span><br>
            <span class="text-sm">{!! nl2br(e($shipping->origin->address . ', ' . $shipping->origin->subdistrict . ', ' . $shipping->origin->district . ', ' . $shipping->origin->city . ', ' . $shipping->origin->province . ' (' . $shipping->origin->postal_code . ')')) !!}</span>
        </div>

        {{-- Recipient --}}
        <div class="py-1 w-1-2">
            <span class="text-sm">To:</span><br>
            <span class="font-bold text-base">{{ $shipping->destination->name }}</span><br>
            <span class="font-bold text-sm">{{ $shipping->destination->phone }}</span><br>
            <span class="text-sm">{!! nl2br(e($shipping->destination->address . ', ' . $shipping->destination->subdistrict . ', ' . $shipping->destination->district . ', ' . $shipping->destination->city . ', ' . $shipping->destination->province . ' (' . $shipping->destination->postal_code . ')')) !!}</span>
        </div>
    </div>

    {{-- Package Information --}}
    <div class="py-1 border-t text-base compact-section">
        <div class="justify-between items-center">
            <div style="display: table-cell; vertical-align: middle;">
                <span class="font-bold text-sm">PACKAGE DIMENSIONS:</span><br>
                <span class="text-sm">{{ $shipping->dimension->length ?? 0 }} × {{ $shipping->dimension->width ?? 0 }} × {{ $shipping->dimension->height ?? 0 }} cm</span>
            </div>
            <div style="display: table-cell; vertical-align: middle; text-align: right;">
                <span class="font-bold text-sm">SHIPPING DATE:</span><br>
                <span class="text-sm">{{ $shipping->date->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    <div class="py-1 border-y text-base compact-section">
        <span class="text-sm">INSTRUCTIONS:</span>
        <span class="font-bold text-sm">{{ $shipping->note ?? '-' }}</span>
    </div>

    {{-- Order ID --}}
    <div class="mt-1 mb-1 justify-between compact-section">
        <div class="text-xl font-bold" style="display: table-cell; width: 80px; vertical-align: middle;">
            Order ID
        </div>
        <div style="display: table-cell; vertical-align: middle; text-align: center;">
            @php
                $orderId = $shipping->trx_id;
                $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                echo '<img style="width: 100%; height: 20px;" src="data:image/png;base64,' .
                    base64_encode($generator->getBarcode($orderId, $generator::TYPE_CODE_128)) .
                    '">';
            @endphp
            <h4 class="font-bold text-sm tracking-wider text-center compact-text">
                {{ mb_strtoupper($orderId) }}
            </h4>
        </div>
    </div>
</section>

</body>
</html>

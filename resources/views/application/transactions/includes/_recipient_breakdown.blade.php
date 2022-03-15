<div class="mb-0" id="recipient-breakdown">
    <!--begin::Row-->
    <div class="row mb-5">
        <!--begin::Col-->
        <div class="col">
            <label class="form-label fw-bolder fs-6 text-gray-700">Recipient</label>
            <select name="recipient_uuid" aria-label="Select a recipient" data-control="select2" data-placeholder="Select recipient" class="form-select form-select-solid fs-1 @error('recipient_uuid') is-invalid @enderror">
                <option value="" selected></option>
                @foreach ($recipients as $recipient)
                    <option {{ (old('recipient_uuid') == $recipient['uuid']) ? 'selected' : '' }} value="{{ $recipient['uuid'] }}">{{ $recipient['name'] }}</option>
                @endforeach
            </select>
            @error('recipient_uuid')
                <x-alert :message="$message" />
            @enderror
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col">
            <label class="form-label fw-bolder fs-6 text-gray-700">Target Currency</label>
            <select name="target_currency_id" aria-label="Select a currecncy" data-control="select2" data-placeholder="Select currency" class="form-select form-select-solid fs-1 @error('target_currency_id') is-invalid @enderror" id="target-currency-id">
                <option value="" selected disabled></option>
                @foreach ($currencies as $currency)
                    <option  value="{{ $currency['id'] }}" {{ (old('target_currency_id') == $currency['id']) ? 'selected' : '' }} @if($sourceCurrency['id'] == $currency['id']) selected @endif><b>{{$currency['code'] }}</b>&#160;-&#160;{{ $currency['name'] }}</option>
                @endforeach
            </select>
            @error('target_currency_id')
                <x-alert :message="$message" />
            @enderror
        </div>
        <!--end::Col-->
    </div>
    
    <!--end::Row-->
    <button type="submit" class="btn btn-primary w-100">
    <!--begin::Svg Icon | path: icons/duotune/general/gen016.svg-->
    <span class="svg-icon svg-icon-3">
        <inline-svg></inline-svg>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M15.43 8.56949L10.744 15.1395C10.6422 15.282 10.5804 15.4492 10.5651 15.6236C10.5498 15.7981 10.5815 15.9734 10.657 16.1315L13.194 21.4425C13.2737 21.6097 13.3991 21.751 13.5557 21.8499C13.7123 21.9488 13.8938 22.0014 14.079 22.0015H14.117C14.3087 21.9941 14.4941 21.9307 14.6502 21.8191C14.8062 21.7075 14.9261 21.5526 14.995 21.3735L21.933 3.33649C22.0011 3.15918 22.0164 2.96594 21.977 2.78013C21.9376 2.59432 21.8452 2.4239 21.711 2.28949L15.43 8.56949Z" fill="black" />
            <path opacity="0.3" d="M20.664 2.06648L2.62602 9.00148C2.44768 9.07085 2.29348 9.19082 2.1824 9.34663C2.07131 9.50244 2.00818 9.68731 2.00074 9.87853C1.99331 10.0697 2.04189 10.259 2.14054 10.4229C2.23919 10.5869 2.38359 10.7185 2.55601 10.8015L7.86601 13.3365C8.02383 13.4126 8.19925 13.4448 8.37382 13.4297C8.54839 13.4145 8.71565 13.3526 8.85801 13.2505L15.43 8.56548L21.711 2.28448C21.5762 2.15096 21.4055 2.05932 21.2198 2.02064C21.034 1.98196 20.8409 1.99788 20.664 2.06648Z" fill="black" />
        </svg>
    </span>
    <!--end::Svg Icon-->Transfer</button>
</div>

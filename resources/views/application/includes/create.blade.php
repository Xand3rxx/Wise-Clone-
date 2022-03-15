@extends('layouts.app')
@section('title', 'Create Transaction')
@section('content')
<div class="toolbar py-5 py-lg-5" id="kt_toolbar">
    <!--begin::Container-->
    <div id="kt_toolbar_container" class="container-xxl d-flex flex-stack flex-wrap">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column me-3">
            <!--begin::Title-->
            <h1 class="d-flex text-dark fw-bolder my-1 fs-3">Create</h1>
            <!--end::Title-->
            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-dot fw-bold text-gray-600 fs-7 my-1">
                <!--begin::Item-->
                <li class="breadcrumb-item text-gray-600">
                    <a href="{{ route('home') }}" class="text-gray-600 text-hover-primary">Dashboard</a>
                </li>
                <!--end::Item-->
                <!--begin::Item-->
                <li class="breadcrumb-item text-gray-600">Transaction</li>
                <!--end::Item-->
                <!--begin::Item-->
                <li class="breadcrumb-item text-gray-500">Create Transaction</li>
                <!--end::Item-->
            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--end::Page title-->
    </div>
    <!--end::Container-->
</div>

<div id="kt_content_container" class="d-flex flex-column-fluid align-items-start container-xxl">
    <!--begin::Post-->
    <div class="content flex-row-fluid" id="kt_content">
        <!--begin::Layout-->
        <div class="d-flex flex-column flex-lg-row">
            <!--begin::Content-->
            <div class="flex-lg-row-fluid mb-10 mb-lg-0 me-lg-6 me-xl-6">
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card body-->
                    <div class="card-body p-12">
                        <!--begin::Form-->
                        <div class="flex-lg-auto min-w-lg-300px">
									<!--begin::Card-->
									<div class="card" data-kt-sticky="true" data-kt-sticky-name="invoice" data-kt-sticky-offset="{default: false, lg: '200px'}" data-kt-sticky-width="{lg: '250px', lg: '300px'}" data-kt-sticky-left="auto" data-kt-sticky-top="150px" data-kt-sticky-animation="false" data-kt-sticky-zindex="95">
										<!--begin::Card body-->
										<div class="card-body p-10">

											<!--begin::Input group-->
											<div class="mb-10">
												<!--begin::Label-->
												<label class="form-label fw-bolder fs-6 text-gray-700">Select your source currency and enter a valid amount</label>
												<!--end::Label-->
												<!--begin::Select-->
                                                <div class="input-group flex-nowrap">
                                                    <input type="text" class="form-control form-control-solid" placeholder="1,000">

                                                    <div class="overflow-hidden flex-grow-1">
                                                        <select name="currnecy" aria-label="Select a currecncy" data-control="select2" data-placeholder="Select currency" class="form-select form-select-solid">
                                                            <option value="" selceted disabled></option>
                                                            @foreach ($currencies as $currency)
                                                            <option  value="{{ $currency['name'] }}">
                                                            <b>{{$currency['code'] }}</b>&#160;-&#160;{{ $currency['name'] }}</option>
                                                            @endforeach
                                                            {{-- data-kt-flag="{{ $currency->flag()->url }}" --}}
                                                        </select>
                                                    </div>
                                            </div>
												<!--end::Select-->
											</div>
											<!--end::Input group-->
											<!--begin::Separator-->
											<div class="separator separator-dashed mb-8"></div>
											<!--end::Separator-->
											<!--begin::Input group-->
											<div class="mb-8">
												<!--begin::Option-->
												<label class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack mb-5">
													<span class="form-check-label ms-0 fw-bolder fs-6 text-gray-700">Payment method</span>
													<input class="form-check-input" type="checkbox" checked="checked" value="" />
												</label>
												<!--end::Option-->
												<!--begin::Option-->
												<label class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack mb-5">
													<span class="form-check-label ms-0 fw-bolder fs-6 text-gray-700">Late fees</span>
													<input class="form-check-input" type="checkbox" value="" />
												</label>
												<!--end::Option-->
												<!--begin::Option-->
												<label class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
													<span class="form-check-label ms-0 fw-bolder fs-6 text-gray-700">Notes</span>
													<input class="form-check-input" type="checkbox" value="" />
												</label>
												<!--end::Option-->
											</div>
											<!--end::Input group-->
											<!--begin::Separator-->
											<div class="separator separator-dashed mb-8"></div>
											<!--end::Separator-->
											<!--begin::Actions-->
											<div class="mb-0">
												<!--begin::Row-->
												<div class="row mb-5">
													<!--begin::Col-->
													<div class="col">
														<a href="#" class="btn btn-light btn-active-light-primary w-100">Preview</a>
													</div>
													<!--end::Col-->
													<!--begin::Col-->
													<div class="col">
														<a href="#" class="btn btn-light btn-active-light-primary w-100">Download</a>
													</div>
													<!--end::Col-->
												</div>
												<!--end::Row-->
												<button type="submit" href="#" class="btn btn-primary w-100" id="kt_invoice_submit_button">
												<!--begin::Svg Icon | path: icons/duotune/general/gen016.svg-->
												<span class="svg-icon svg-icon-3">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
														<path d="M15.43 8.56949L10.744 15.1395C10.6422 15.282 10.5804 15.4492 10.5651 15.6236C10.5498 15.7981 10.5815 15.9734 10.657 16.1315L13.194 21.4425C13.2737 21.6097 13.3991 21.751 13.5557 21.8499C13.7123 21.9488 13.8938 22.0014 14.079 22.0015H14.117C14.3087 21.9941 14.4941 21.9307 14.6502 21.8191C14.8062 21.7075 14.9261 21.5526 14.995 21.3735L21.933 3.33649C22.0011 3.15918 22.0164 2.96594 21.977 2.78013C21.9376 2.59432 21.8452 2.4239 21.711 2.28949L15.43 8.56949Z" fill="black" />
														<path opacity="0.3" d="M20.664 2.06648L2.62602 9.00148C2.44768 9.07085 2.29348 9.19082 2.1824 9.34663C2.07131 9.50244 2.00818 9.68731 2.00074 9.87853C1.99331 10.0697 2.04189 10.259 2.14054 10.4229C2.23919 10.5869 2.38359 10.7185 2.55601 10.8015L7.86601 13.3365C8.02383 13.4126 8.19925 13.4448 8.37382 13.4297C8.54839 13.4145 8.71565 13.3526 8.85801 13.2505L15.43 8.56548L21.711 2.28448C21.5762 2.15096 21.4055 2.05932 21.2198 2.02064C21.034 1.98196 20.8409 1.99788 20.664 2.06648Z" fill="black" />
													</svg>
												</span>
												<!--end::Svg Icon-->Send Invoice</button>
											</div>
											<!--end::Actions-->
										</div>
										<!--end::Card body-->
									</div>
									<!--end::Card-->
								</div>
                        <!--end::Form-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Content-->
            <!--begin::Sidebar-->
            <div class="flex-lg-auto min-w-lg-300px">
            </div>
            <!--end::Sidebar-->
        </div>
        <!--end::Layout-->
    </div>
    <!--end::Post-->
</div>

@endsection



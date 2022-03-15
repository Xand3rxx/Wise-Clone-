<!-- Ec breadcrumb start -->
<div class="sticky-header-next-sec  ec-breadcrumb section-space-mb">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="row ec_breadcrumb_inner">
                    <div class="col-md-6 col-sm-12">
                        <h2 class="ec-breadcrumb-title">{{ $name }}</h2>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <!-- ec-breadcrumb-list start -->
                        <ul class="ec-breadcrumb-list">
                            <li class="ec-breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
                            @if (!empty($category))
                                <li class="ec-breadcrumb-item"><a href="{{ route('frontend.product.index') }}">Products</a></li>
                                <li class="ec-breadcrumb-item active">{{ $category }}</li>
                            @else
                                <li class="ec-breadcrumb-item active"><a href="{{ route('frontend.product.index') }}">Products</a></li>
                            @endif
                        </ul>
                        <!-- ec-breadcrumb-list end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Ec breadcrumb end -->

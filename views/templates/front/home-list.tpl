

<section class="featured-products  clearfix">
    <h3 class="h3 products-section-title ">
        {l s='Productos de Portada' d='Shop.Theme.Catalog'}
    </h3>
    <div class="products js-product__slider">
        {foreach from=$productsForTemplate item="product"}
            {include file="catalog/_partials/miniatures/product.tpl" product=$product}
        {/foreach}
    </div>
</section>

<script>
    $(document).ready(function(){
        $('.js-product__slider ').slick({
            centerMode: true,
            infinite: true,
            slidesToShow: 4,
            speed: 500,
            variableWidth: false,
            responsive: [
                {
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1,
                        centerMode: true,
                        infinite: true,
                        dots: false
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 1,
                        centerMode: false,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        centerMode: false,
                        slidesToScroll: 1
                    }
                }
                ]

        });
    });

</script>



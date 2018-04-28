
<div class="panel col-md-6">



    <h3>{l s="Products en la Home"}</h3>
    <table class="table">
        <tr>
            <th>{l s="Posicion"}</th>
            <th>{l s="Nombre"}</th>
            <th>{l s="Acciones"}</th>
        </tr>

        {assign var="count" value=0}
        {foreach $productsHome  as $product}
            <tr>
                <td>
                    {$positions[$count]}
                </td>
                <td>
                    {$product->name[$lang]}
                </td>
                <td>
                    <a href="{$link->getAdminLink('AdminModules')}&configure=os_products_home&deleteProduct={$product->id}">{l s="Borrar"}</a>
                    <a class="js-change-position" href="{$link->getAdminLink('AdminModules')}&configure=os_products_home&changePosition={$product->id}&positionDestininy=">{l s="Subir posición"}</a>


                </td>


            </tr>
            {$count = $count + 1}
        {/foreach}
    </table>
    <div>
        {assign var="count" value=0}

        <h3>{l s="Posición destino"}</h3>
        <select id="position-destiny">
            {foreach $productsHome as $posicion}
                {$count = $count+1}
                <option value="{$count}">{$count}</option>
                {/foreach}
        </select>
    </div>
</div>

<script>
    $(document).ready(function(){

        $('.js-change-position').click(function(event){

            event.preventDefault();

            var selectPosition = $('#position-destiny').find(":selected").val();
            var url =  $(this).attr('href')+selectPosition;

            location.href = url;




        });

    });
</script>
<div class="panel col-md-6">

    <h3>{l s="Productos Disponibles"}</h3>
    <table class="table">
        <tr>
            <th>{l s="ID"}</th>
            <th>{l s="Nombre"}</th>
            <th>{l s="Acciones"}</th>
        </tr>
{foreach $products  as $product}
    <tr>
        <td>
            {$product['id_product']}
        </td>
        <td>
            {$product['name']}
        </td>
        <td>
            <a href="{$link->getAdminLink('AdminModules')}&configure=os_products_home&addProduct={$product['id_product']}">{l s="Añadir Producto"}</a>
        </td>

    </tr>
    {/foreach}
    </table>

</div>




</div>
<?php
$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();
$conn = $installer->getConnection();

if ($this->tableExists($this->getTable('dataflow_profile'))) {
    $this->run("
    insert into {$this->getTable('dataflow_profile')} (name, actions_xml, created_at, updated_at) 
    values ('".utf8_encode('Exportar Produtos Catálogo - CSV')."','"
    .utf8_encode('<action type="catalog/convert_adapter_product" method="load"></action>

<action type="catalog/convert_parser_product" method="unparse">
    <var name="url_field"><![CDATA[0]]></var>
</action>

<action type="coobus_catalog/convert_parser_product_number" method="unparse">
    <var name="decimal_separator"><![CDATA[,]]></var>
</action>

<action type="dataflow/convert_mapper_column" method="map">
    <var name="map">
        <map name="sku"><![CDATA[Ref.]]></map>
        <map name="name"><![CDATA[Nome]]></map>
        <map name="price"><![CDATA[Preço]]></map>
        <map name="qty"><![CDATA[Quant.]]></map>
        <map name="short_description"><![CDATA[Resumo]]></map>
        <map name="description"><![CDATA[Descrição]]></map>
        <map name="categories"><![CDATA[Categoria]]></map>
        <map name="weight"><![CDATA[Peso (gr)]]></map>
        <map name="backorders"><![CDATA[Aceitar Encomenda]]></map>
        <map name="min_sale_qty"><![CDATA[Qtde mín. no pedido]]></map>
        <map name="volume_comprimento"><![CDATA[Comprimento (cm)]]></map>
        <map name="volume_largura"><![CDATA[Largura (cm)]]></map>
        <map name="volume_altura"><![CDATA[Altura (cm)]]></map>

        <!--map name="attribute_set"><![CDATA[Grupo]]></map>
        <map name="type"><![CDATA[Tipo]]></map>
        <map name="is_in_stock"><![CDATA[Disponível]]></map-->
    </var>
    <var name="_only_specified">true</var>
</action>

<action type="coobus_catalog/convert_parser_csv" method="unparse">
    <var name="delimiter"><![CDATA[;]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames">true</var>
</action>')."',
now(),
now()
    );");
$this->setConfigData('catalog/management/exportprofile_csv', $conn->lastInsertId());

$this->run("    
    insert into {$this->getTable('dataflow_profile')} (name, actions_xml, created_at, updated_at) 
    values ('".utf8_encode('Exportar Produtos Catálogo - XML')."','"
    .utf8_encode('<action type="catalog/convert_adapter_product" method="load"></action>

<action type="catalog/convert_parser_product" method="unparse">
    <var name="url_field"><![CDATA[0]]></var>
</action>

<action type="coobus_catalog/convert_parser_product_number" method="unparse">
    <var name="decimal_separator"><![CDATA[,]]></var>
</action>

<action type="dataflow/convert_mapper_column" method="map">
    <var name="map">
        <map name="sku"><![CDATA[Ref.]]></map>
        <map name="name"><![CDATA[Nome]]></map>
        <map name="price"><![CDATA[Preço]]></map>
        <map name="qty"><![CDATA[Quant.]]></map>
        <map name="short_description"><![CDATA[Resumo]]></map>
        <map name="description"><![CDATA[Descrição]]></map>
        <map name="categories"><![CDATA[Categoria]]></map>
        <map name="weight"><![CDATA[Peso (gr)]]></map>
        <map name="backorders"><![CDATA[Aceitar Encomenda]]></map>
        <map name="min_sale_qty"><![CDATA[Qtde mín. no pedido]]></map>
        <map name="volume_comprimento"><![CDATA[Comprimento (cm)]]></map>
        <map name="volume_largura"><![CDATA[Largura (cm)]]></map>
        <map name="volume_altura"><![CDATA[Altura (cm)]]></map>

        <!--map name="attribute_set"><![CDATA[Grupo]]></map>
        <map name="type"><![CDATA[Tipo]]></map>
        <map name="is_in_stock"><![CDATA[Disponível]]></map-->
    </var>
    <var name="_only_specified">true</var>
</action>

<action type="coobus_catalog/convert_parser_xml_excel" method="unparse">
    <var name="delimiter"><![CDATA[;]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames">true</var>
    <var name="decimal_separator"><![CDATA[.]]></var>
</action>')."',
now(),
now()
    );");
$this->setConfigData('catalog/management/exportprofile_xml', $conn->lastInsertId());

$this->run("    
    insert into {$this->getTable('dataflow_profile')} (name, actions_xml, created_at, updated_at) 
    values ('".utf8_encode('Importar Produtos Catálogo - CSV')."','"
    .utf8_encode('<action type="dataflow/convert_adapter_io" method="load">
    <var name="type">file</var>
    <var name="path">var/import</var>
    <var name="filename"><![CDATA[produtos.csv]]></var>
    <var name="format"><![CDATA[csv]]></var>
</action>

<action type="coobus_catalog/convert_parser_csv" method="parse">
    <var name="delimiter"><![CDATA[;]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames">true</var>
    <var name="_only_specified">true</var>
    <var name="number_of_records">1</var>
    <var name="decimal_separator"><![CDATA[,]]></var>
    <var name="default">
        <default name="store"><![CDATA[{{storeCode}}]]></default>
        <default name="is_in_stock"><![CDATA[1]]></default>
        <default name="visibility"><![CDATA[Catálogo, Busca]]></default>
        <default name="tax_class_id"><![CDATA[Nenhum]]></default>
        <default name="type"><![CDATA[simple]]></default>
        <default name="attribute_set"><![CDATA[Comum]]></default>
    </var>
    <var name="map">
        <map name="Ref."><![CDATA[sku]]></map>
        <map name="Nome"><![CDATA[name]]></map>
        <map name="Preço"><![CDATA[price]]></map>
        <map name="Quant."><![CDATA[qty]]></map>
        <map name="Resumo"><![CDATA[short_description]]></map>
        <map name="Descrição"><![CDATA[description]]></map>
        <map name="Categoria"><![CDATA[categories]]></map>
        <map name="Peso (gr)"><![CDATA[weight]]></map>
        <map name="Aceitar Encomenda"><![CDATA[backorders]]></map>
        <map name="Qtde mín. no pedido"><![CDATA[min_sale_qty]]></map>
        <map name="Comprimento (cm)"><![CDATA[volume_comprimento]]></map>
        <map name="Largura (cm)"><![CDATA[volume_largura]]></map>
        <map name="Altura (cm)"><![CDATA[volume_altura]]></map>
        <map name="Imagem"><![CDATA[image]]></map>
        <map name="Imagem (reduzida)"><![CDATA[thumbnail]]></map>
        <map name="Imagem (mini)"><![CDATA[small_image]]></map>
        <map name="loja"><![CDATA[store]]></map>
        <map name="Grupo"><![CDATA[attribute_set]]></map>
        <map name="Tipo"><![CDATA[type]]></map>
        <map name="Classe de Imposto"><![CDATA[tax_class_id]]></map>
        <map name="Exibir em"><![CDATA[visibility]]></map>
        <map name="Disponível"><![CDATA[is_in_stock]]></map>
    </var>
    <var name="adapter">catalog/convert_adapter_product</var>
    <var name="method">parse</var>
</action>')."',
now(),
now()
    );");
    
}

$installer->endSetup();

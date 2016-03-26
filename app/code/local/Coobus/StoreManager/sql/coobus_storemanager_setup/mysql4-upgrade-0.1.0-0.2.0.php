<?php
$installer = $this;

$installer->startSetup();
$conn = $installer->getConnection();

$rowPaymentAvailable = $this->getTableRow('sales/order_status','status','payment_available' );

if (  !$rowPaymentAvailable  ) {
    $this->run("
    insert into {$this->getTable('sales/order_status')} (status, label) 
    values ('payment_available','Pagamento Liberado');
    insert into {$this->getTable('sales/order_status_state')} (status, state, is_default) 
    values ('payment_available','processing',0);
");
}
$rowSharing = $this->getTableRow('sales/order_status','status','sharing' );

if (  !$rowSharing ) {
    $this->run("
    insert into {$this->getTable('sales/order_status')} (status, label) 
    values ('sharing','Aguardando Comissionamento');
    insert into {$this->getTable('sales/order_status_state')} (status, state, is_default) 
    values ('sharing','complete',0);
");
}
$installer->endSetup();

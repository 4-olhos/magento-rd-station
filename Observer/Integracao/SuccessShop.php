<?php 

namespace QuatroOlhos\RdStation\Observer\Integracao;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Json\Helper\Data;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Sales\Api\OrderRepositoryInterface;


class SuccessShop implements ObserverInterface {

  protected $jsonHelper;
  protected $scopeConfig;
  protected $customerRepositoryInterface;
  protected $checkoutSession;
  protected $orderRepositoryInterface;
  protected $customer;

  public function __construct(
    Data $jsonHelper, 
    ScopeConfigInterface $scopeConfig,
    CustomerRepositoryInterface $customerRepositoryInterface,
    OrderRepositoryInterface $orderRepositoryInterface
  ) {
      $this->jsonHelper = $jsonHelper;
      $this->scopeConfig = $scopeConfig;
      $this->_orderRepositoryInterface = $orderRepositoryInterface;
      $this->_customerRepositoryInterface = $customerRepositoryInterface;
      $this->jsonHelper = $jsonHelper;
      $this->scopeConfig = $scopeConfig;
    }

  public function execute(Observer $observer) {
    $orderIds = $observer->getEvent()->getOrderIds();
    if (count($orderIds)) {
      $orderId = $orderIds[0];            
      $order = $this->_orderRepositoryInterface->get($orderId);
      $customerId = $order->getCustomerId();
      $customers = $this->_customerRepositoryInterface->getById($customerId);
      $produtos = '';
      foreach ($order->getAllVisibleItems() as $item){
        $produtos .= $item->getName().', ';
      } 
      $data = array(
        'token_rdstation' => $this->scopeConfig->getValue('quatro_rdstation/general/token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
        'identificador' => $this->scopeConfig->getValue('quatro_rdstation/identificador/completed', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
        'email' => $customers->getEmail(),
        'Nome' => $customers->getFirstname().' '.$customers->getLastname(),
        'Telefone' => $order->getShippingAddress()->getTelephone(),
        'total_compra' => $order->getData('grand_total'),
        'quantidade_produtos' => $order->getData('total_item_count'),
        'produtos' => $produtos,
        'Id_order' => $orderId
      );
      $encodeData = $this->jsonHelper->jsonEncode($data);
      $url = 'https://www.rdstation.com.br/api/1.3/conversions';
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $encodeData);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_exec($ch);
      curl_close($ch);
    }
  }
}

?>
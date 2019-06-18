<?php 

namespace QuatroOlhos\RdStation\Observer\Integracao;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Json\Helper\Data;

class CreateCustomer implements ObserverInterface {

  protected $jsonHelper;
  protected $scopeConfig;

  public function __construct(
    Data $jsonHelper, 
    ScopeConfigInterface $scopeConfig
  ) {
      $this->jsonHelper = $jsonHelper;
      $this->scopeConfig = $scopeConfig;
  }

  public function execute(Observer $observer) {
    $customer = $observer->getEvent()->getCustomer();
    $nome = $customer->getFirstname().' '.$customer->getLastname();
    $email = $customer->getEmail();
    $data = array(
      'token_rdstation' => $this->scopeConfig->getValue('quatro_rdstation/general/token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
      'identificador' => $this->scopeConfig->getValue('quatro_rdstation/identificador/customer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
      'email' => $email,
      'Nome' => $nome
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

?>
# Modulo Magento e RD Station

### Instalação do Modulo.
#### Preparação do pasta no magento
```bash 
cd app/code
mkdir QuatroOlhos
git clone https://github.com/4-olhos/magento-rd-station.git RdStation
```
#### Nós atualizamos nosso magento
Nós rodamos bin/magento para atualizar nosso banco de dados e limpar o cache do nosso magento.
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
```
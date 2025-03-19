# Módulo de Pagamento Mercado Pago para WHMCS

Este módulo permite aceitar pagamentos via Mercado Pago (PIX) no WHMCS, utilizando conexão direta via socket para contornar problemas de SSL.

## Características

- Geração de QR Code PIX para pagamentos
- Verificação automática de status de pagamento
- Suporte a notificações IPN (Instant Payment Notification)
- Compatível com ambientes com limitações de SSL
- Não requer proxy ou Node.js

## Requisitos

- WHMCS 7.0 ou superior
- PHP 7.0 ou superior
- Conta no Mercado Pago com acesso à API
- Permissão para usar a função `fsockopen` no PHP

## Instalação

1. **Faça o download dos arquivos do módulo**

2. **Copie os arquivos para a estrutura do WHMCS**:
   - Copie a pasta `modules` para a raiz do seu WHMCS

3. **Execute o script de instalação**:
   - Acesse `https://seu-whmcs.com/modules/gateways/mercadopago/install.php`
   - Isso criará a tabela necessária no banco de dados

4. **Configure o gateway de pagamento**:
   - Acesse o painel administrativo do WHMCS
   - Vá para "Configurações" > "Pagamentos" > "Gateways de Pagamento"
   - Ative o gateway "Mercado Pago (PIX)"
   - Preencha as configurações necessárias

## Configuração

### Obtenção das credenciais do Mercado Pago

1. Acesse sua conta no [Mercado Pago](https://www.mercadopago.com.br/)
2. Vá para "Seu negócio" > "Credenciais"
3. Obtenha o "Access Token" de produção e teste

### Configuração no WHMCS

1. **Access Token**: Insira o Access Token de produção obtido no Mercado Pago
2. **Modo de Teste**: Marque esta opção para usar o ambiente de testes
3. **Access Token de Teste**: Insira o Access Token de teste obtido no Mercado Pago
4. **Tempo de Expiração**: Defina o tempo de expiração do PIX em minutos (padrão: 30)

## Configuração de IPN (Notificações de Pagamento)

Para receber notificações automáticas de pagamento:

1. Acesse sua conta no Mercado Pago
2. Vá para "Seu negócio" > "Configurações" > "Notificações"
3. Adicione uma nova URL de notificação:
   ```
   https://seu-whmcs.com/modules/gateways/callback/mercadopago.php?action=ipn
   ```

## Solução de Problemas

### Erro de conexão com o Mercado Pago

Se você encontrar erros de conexão:

1. Verifique se a função `fsockopen` está habilitada no PHP
2. Verifique se o Access Token está correto
3. Verifique os logs de atividade do WHMCS para mensagens de erro detalhadas

### QR Code não é gerado

Se o QR Code não for gerado:

1. Verifique se o Access Token tem permissões para criar pagamentos
2. Verifique se a conta do Mercado Pago está ativa e verificada
3. Verifique os logs de atividade do WHMCS para mensagens de erro detalhadas

### Pagamentos não são registrados automaticamente

Se os pagamentos não forem registrados automaticamente:

1. Verifique se a URL de IPN está configurada corretamente
2. Verifique se o servidor permite conexões de entrada do Mercado Pago
3. Verifique os logs de atividade do WHMCS para mensagens de erro detalhadas

## Suporte

Para suporte, entre em contato através do e-mail: seu-email@exemplo.com

## Licença

Este módulo é distribuído sob a licença MIT. 
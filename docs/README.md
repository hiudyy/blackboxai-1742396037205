# Integração Mercado Pago com Baileys WhatsApp

Este projeto permite gerar cobranças via PIX usando a API do Mercado Pago e enviar os QR Codes de pagamento através do WhatsApp usando a biblioteca Baileys.

## Requisitos

- Node.js 14+ instalado
- Conta no Mercado Pago (com acesso às credenciais)
- Número de WhatsApp para o bot

## Instalação

1. Clone este repositório ou crie uma nova pasta e adicione os arquivos

2. Instale as dependências necessárias:

```bash
npm install axios @adiwajshing/baileys pino qrcode-terminal
```

3. Atualize o arquivo `mercadopago.js` com o seu Access Token do Mercado Pago:

```javascript
const ACCESS_TOKEN = "SEU_ACCESS_TOKEN_AQUI";
```

Para obter seu Access Token:
1. Faça login na sua conta do Mercado Pago
2. Vá para Configurações > Credenciais > Produção > Access token
3. Copie o token e substitua no código

## Estrutura do Projeto

- `mercadopago.js` - Módulo para comunicação com a API do Mercado Pago
- `pagamento-baileys.js` - Integração entre Mercado Pago e Baileys
- `exemplo-bot.js` - Exemplo completo de um bot WhatsApp com comandos de pagamento
- `tmp/` - Pasta temporária para armazenar QR Codes gerados

## Como Usar

1. Inicie o bot de exemplo:

```bash
node exemplo-bot.js
```

2. Escaneie o QR Code que aparece no terminal com o WhatsApp do número que deseja usar como bot

3. Envie uma mensagem para o bot de outro número usando:
   - `!pagar 100` para gerar um pagamento de R$ 100,00
   - `!pagar 50 Assinatura Premium` para gerar um pagamento de R$ 50,00 com descrição
   - `!ajuda` para ver os comandos disponíveis

## Customização

Para integrar em seu projeto existente que usa Baileys:

1. Importe as funções de pagamento:
```javascript
const { enviarCobrancaPix, monitorarPagamento } = require('./pagamento-baileys');
```

2. Chame a função de pagamento quando necessário:
```javascript
// sock é a instância do Baileys
// remoteJid é o ID do chat para enviar a mensagem
await enviarCobrancaPix(sock, remoteJid, 100, 'Assinatura Premium');
```

## Segurança

- Guarde seu Access Token com segurança
- Em produção, considere armazenar o token em variáveis de ambiente
- Implemente validações adicionais conforme necessário para seu caso de uso

## Troubleshooting

### Erros Comuns

1. **Erro de conexão**:
   - Verifique sua conexão à internet
   - Confirme que seu Access Token está correto
   - Verifique se sua conta Mercado Pago está ativa

2. **QR Code não aparece**:
   - Certifique-se de que o caminho da pasta `tmp/` exista
   - Verifique permissões de escrita no diretório

3. **Erro ao enviar mensagem**:
   - Verifique se o bot está conectado corretamente
   - Confirme que você tem as permissões necessárias para enviar mensagens

## Limitações

- A API do Mercado Pago tem limites de requisições. Verifique a documentação oficial para mais detalhes.
- Os pagamentos PIX têm validade de 30 minutos por padrão. 
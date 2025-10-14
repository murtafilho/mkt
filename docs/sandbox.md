1. Ambiente Sandbox: Você está em modo TEST
  (MERCADOPAGO_TEST_MODE=true)
  2. CPF Real não funciona: No sandbox, o Mercado Pago rejeita CPFs        
  reais
  3. CPF de Teste: Encontrei na documentação oficial: 19119119100

  O que foi implementado:

  Modifiquei o PaymentService para detectar automaticamente quando está    
   em modo TEST e usar o CPF de teste do Mercado Pago.

  Comportamento agora:
  - ✅ Sandbox/Test: Usa CPF 19119119100 (mesmo que o usuário tenha CPF    
   real)
  - ✅ Produção: Usa o CPF real do usuário
  - ✅ Auto-switch: Muda automaticamente baseado em
  MERCADOPAGO_TEST_MODE
  - ✅ Log transparente: Registra quando está usando CPF de teste

  Arquivo modificado: app/Services/PaymentService.php:170-178

  ---
  🧪 Pode testar agora!

  Tente fazer um checkout com PIX. O sistema vai:
  1. Coletar seu CPF real (para salvar no perfil)
  2. Usar o CPF de teste 19119119100 na requisição para o Mercado Pago     
  3. Receber o QR Code do PIX
  4. No sandbox, o pagamento é aprovado automaticamente

  Quando for para produção, basta mudar MERCADOPAGO_TEST_MODE=false no     
  .env e o sistema vai usar CPFs reais automaticamente.
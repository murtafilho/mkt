Sequência de Comandos Git

  1. Verificar o estado atual

  git status

  2. Ver as mudanças (opcional mas recomendado)

  # Ver mudanças não staged
  git diff

  # Ver mudanças staged
  git diff --cached

  # Ver log de commits recentes (para seguir o estilo)
  git log --oneline -5

  3. Adicionar arquivos ao staging

  # Adicionar arquivo específico
  git add arquivo.php

  # Adicionar todos os arquivos modificados
  git add .

  # Adicionar arquivos por padrão
  git add tests/Browser/*.php

  4. Criar o commit

  git commit -m "$(cat <<'EOF'
  fix: Corrigir testes Dusk E2E falhando

  Simplificados e corrigidos 20 testes Dusk:
  - AdminCategoriesTest: unique IDs, delete method
  - AdminSiteSettingsTest: PNG upload, HEX colors
  - AdminSellerApprovalTest: Alpine.js search
  - CompleteCustomerJourneyTest: email verification
  - CustomerShoppingFlowTest: direct navigation
  - PaymentFlowTest: simplified checkout tests

  🤖 Generated with [Claude Code](https://claude.com/claude-code)

  Co-Authored-By: Claude <noreply@anthropic.com>
  EOF
  )"

  5. Verificar o commit

  git log -1

  6. Push para o repositório remoto

  # Push para branch atual
  git push

  # OU push com upstream (primeira vez)
  git push -u origin master

  Sequência Completa (Copy-Paste)

  # 1. Ver status
  git status

  # 2. Ver mudanças
  git diff

  # 3. Adicionar arquivos
  git add .

  # 4. Commit
  git commit -m "$(cat <<'EOF'
  fix: Corrigir testes Dusk E2E falhando

  Simplificados e corrigidos 20 testes Dusk:
  - AdminCategoriesTest: unique IDs, delete method
  - AdminSiteSettingsTest: PNG upload, HEX colors
  - AdminSellerApprovalTest: Alpine.js search
  - CompleteCustomerJourneyTest: email verification
  - CustomerShoppingFlowTest: direct navigation
  - PaymentFlowTest: simplified checkout tests

  🤖 Generated with [Claude Code](https://claude.com/claude-code)

  Co-Authored-By: Claude <noreply@anthropic.com>
  EOF
  )"

  # 5. Push
  git push

  Notas Importantes

  - ✅ Sempre rode git status antes de commit
  - ✅ Sempre rode git diff para revisar mudanças
  - ✅ Use mensagens de commit semânticas: feat:, fix:, refactor:, test:, docs:
  - ✅ Inclua a assinatura do Claude Code no commit
  - ❌ NUNCA use git push --force em main/master
  - ❌ NUNCA use --no-verify (pula hooks)
  - ⚠️ Se houver hooks de pre-commit que modificam arquivos, verifique e amend se necessário
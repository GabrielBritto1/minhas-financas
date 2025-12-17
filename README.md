# Finanças (Option 1) - Projeto completo

Instalação rápida:
1. Copie o conteúdo para seu servidor (ex: C:\xampp\htdocs\financas ou /var/www/financas)
2. Importe o banco: `mysql -u root -p < install.sql`
3. Ajuste `config.php` com suas credenciais
4. Rode `composer install` para instalar Dompdf (opcional para PDF)
5. Aponte seu virtual host para a pasta `public/` (ou acesse http://localhost/financas/public/index.php)

Observações:
- O projeto não inclui a pasta vendor. Execute `composer install`.
- Já corrigi todas as rotas para usar `index.php?route=...`
- A pasta app contém Views e Actions e não é acessível diretamente pelo navegador.

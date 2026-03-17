@echo off
chcp 65001 >nul
echo.
echo =============================================
echo  Criando estrutura de pastas do projeto "despesas"
echo =============================================
echo.

:: Criar pastas
mkdir "despesas" 2>nul
mkdir "despesas\config" 2>nul
mkdir "despesas\classes" 2>nul
mkdir "despesas\api" 2>nul
mkdir "despesas\assets\css" 2>nul
mkdir "despesas\assets\js" 2>nul
mkdir "despesas\includes" 2>nul

:: Criar arquivos vazios
type nul > "despesas\config\database.php"
type nul > "despesas\config\email.php"

type nul > "despesas\classes\Database.php"
type nul > "despesas\classes\Despesa.php"
type nul > "despesas\classes\EmailService.php"

type nul > "despesas\api\boletos.php"

type nul > "despesas\assets\css\style.css"
type nul > "despesas\assets\js\script.js"

type nul > "despesas\includes\header.php"
type nul > "despesas\includes\footer.php"

type nul > "despesas\index.php"
type nul > "despesas\adicionar.php"
type nul > "despesas\editar.php"
type nul > "despesas\relatorio.php"
type nul > "despesas\enviar_relatorio.php"

echo ✅ Estrutura criada com sucesso!
echo.
echo Pasta raiz: despesas\
echo.
echo Verifique a estrutura gerada.
echo.
pause
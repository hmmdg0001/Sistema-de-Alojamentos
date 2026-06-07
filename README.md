# Alojamentos Online

Este é um projeto académico desenvolvido para a unidade curricular de **Programação WEB I & II**. Trata-se de uma aplicação web de reserva e gestão de alojamentos locais, construída em PHP e MySQL.

A plataforma permite que os utilizadores se registem como **Clientes** para procurar e reservar alojamentos, ou como **Gestores** para disponibilizar e gerir as suas próprias propriedades.

## Funcionalidades Principais

* **Autenticação e Perfis de Utilizador**:
  * Registo de novos utilizadores com distinção de funções (Clientes e Gestores).
  * Início de sessão seguro com cifragem de palavras-passe.
  * Barra de navegação dinâmica em função do perfil ativo.

* **Pesquisa e Consulta**:
  * Catálogo de alojamentos disponíveis com detalhes de preço, localização, estadia mínima e classificação média.
  * Página individual de cada alojamento com informações detalhadas e avaliações de outros utilizadores.

* **Gestão de Reservas (Clientes)**:
  * Criação de novas reservas com cálculo automático do preço total.
  * Controlo de disponibilidade para evitar a sobreposição de datas.
  * Consulta do histórico e estado das reservas efetuadas.
  * Possibilidade de cancelar reservas.
  * Atribuição de avaliações e comentários após a estadia.

* **Gestão de Alojamentos e Reservas (Gestores)**:
  * Criação, edição e ativação/desativação de alojamentos.
  * Aprovação ou rejeição de reservas efetuadas pelos clientes.

## Estrutura da Base de Dados
A base de dados (`tpfinal_db`) é constituída pelas seguintes tabelas:
* `utilizadores`: Registo dos perfis (clientes, gestores e administradores).
* `alojamentos`: Informação dos imóveis registados na plataforma.
* `reservas`: Detalhes das reservas e respetivo estado (pendente, confirmada, cancelada).
* `avaliacoes`: Classificações e comentários deixados pelos clientes.
* `datas_bloqueadas`: Datas em que os alojamentos se encontram indisponíveis.

## Tecnologias Utilizadas
* **Linguagem Principal**: PHP
* **Base de Dados**: MySQL (através da extensão PDO)
* **Interface**: HTML5 e CSS3 (Design responsivo e limpo)

## Como Executar o Projeto Localmente
1. **Instalar o Servidor**: É necessário ter um servidor local com suporte para PHP e MySQL, como o XAMPP.
2. **Copiar os Ficheiros**: Coloque a pasta do projeto dentro do diretório `htdocs` do XAMPP.
3. **Importar a Base de Dados**:
   * Crie uma base de dados no MySQL com o nome `tpfinal_db`.
   * Crie as tabelas necessárias de acordo com o esquema da aplicação.
4. **Configurar a Ligação**:
   * Se necessário, ajuste os dados de ligação no ficheiro [config.php](file:///c:/xampp/htdocs/WEB_FINAL/includes/config.php).
5. **Aceder à Aplicação**: Abra o seu navegador e aceda a `http://localhost/WEB_FINAL/`.

---
*Este projeto tem fins exclusivamente académicos.*

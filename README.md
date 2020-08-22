# Email Tickets Service

Serviço de criação chamados a partir da troca de e-mails.

## Setup de desenvolvimento

Antes de iniciar o serviço, é necessário fazer a instalação das dependências do projeto. Para fazê-lo, execute o comando do Composer a seguir:

```shellscript
$ composer install
```

Para executar o projeto no ambiente de desenvolvimento, basta executar o comando a seguir:

```shellscript
$ docker-compose up
```

## Para produção

### Gerando imagem partir do Dockerfile

O arquivo Dockerfile, localizado na raíz do projeto, ficará por containerizar toda a aplicação para que ele possa ser usada em clusters.
Para gerar uma imagem da aplicação containerizada, execute o comando abaixo na raíz do projeto:

```shellscript
$ docker build -t email_tickets_service .
```

### Usando Docker Compose

Caso queira usar o Docker Compose para rodar a aplicação em produção, basta substituir o Dockerfile-dev indicado no arquivo docker-compose.yml
pelo Dockerfile de produção.

## Configuração

A configuração da aplicação é feita por meio de variáveis de ambiente, que deve ser feito na criação do container.

### Variáveis de ambiente

| Nome                    | Tipo   | Obrigatório | Descrição                                                                     |
|-------------------------|--------|-------------|-------------------------------------------------------------------------------|
| <b>DATABASE_URL</b>     | string | Sim         | URL de conexão do banco de dados MySQL                                        |
| <b>IMAP_HOST</b>        | string | Sim         | Caminho do servidor de caixa de e-mail                                        |
| <b>IMAP_USERNAME</b>    | string | Sim         | E-mail do usuário para acessar a caixa de e-mail                              |
| <b>IMAP_PASSWORD</b>    | string | Sim         | Senha do usuário para acessar a caixa de e-mail                               |
| <b>WEBHOOK_ENDPOINT</b> | string | Sim         | URL que deve receber os e-mails                                               |
| <b>MAX_ATTEMPTS</b>     | int    | Sim         | Número máximo de tentativas de envio para URL configurada em WEBHOOK_ENDPOINT |

### IMAP

IMAP é o protocolo utilizado para buscar os e-mails na caixa de e-mail neste serviço e esta seção terá informações
importantes sobre sua configuração. Antes de seguir com este guia de configuração você deve habilitar o acesso da caixa de e-mail
por este protocolo no seu provedor de e-mail.

#### Caixa de e-mail

O caminho do servidor da caixa de e-mail segue o seguinte formato:

{<dominio_do_servidor>\[:\<porta\>\]\[\<flags\>\]}\[<nome_caixa_email>\]

Para a lista completa de flags [clique aqui](https://www.php.net/manual/en/function.imap-open.php).

##### Exemplos
- *Gmail:* {imap.gmail.com:993/imap/ssl}INBOX
- *Outlook/Hotmail:* {outlook.office365.com:993/imap/ssl}INBOX
- *Yahoo:* {imap.mail.yahoo.com:993/imap/ssl}INBOX

#### Gmail

Devido a uma [incompatiblidade](https://www.reddit.com/r/PHPhelp/comments/be0jq9/imap_gmail_ssl_no_sni_provided_please_fix_your/)
com a biblioteca IMAP, alguns passos extras devem ser realizados caso queira configurar um e-mail do Gmail.

##### 1) Pulando verificação de certificado

Para evitar esta etapa no servidor de e-mail você deve adicionar a flag */novalidate-cert*. Resultado: {imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX

##### 2) Habilitar Less secure apps

Para habilitar este recurso acesse [este link](https://myaccount.google.com/lesssecureapps).

## Dados de envio

Esta seção irá detalhar os dados que serão enviados para a URL configurada.

### Message

| Atributo           | Tipo                        | Descrição                                     |
|--------------------|-----------------------------|-----------------------------------------------|
| <b>id</b>          | string int                  | Identificador da mensagem                     |
| <b>name</b>        | string                      | Nome do remetente                             |
| <b>email</b>       | string                      | E-mail do remetente                           |
| <b>body</b>        | string                      | Corpo da mensagem                             |
| <b>thread_id</b>   | string                      | Identificador da conversa                     |
| <b>contract_id</b> | string                      | Identificador do contrato                     |
| <b>sent_at</b>     | date                        | Data de envio da mensagem no formato ISO 8601 |
| <b>attachments</b> | [Attachment](#attachment)[] | Anexos da mensagem                            |

### Attachment

| Atributo                   | Tipo         | Descrição                                     |
|----------------------------|--------------|-----------------------------------------------|
| <b>id</b>                  | string int   | Identificador do anexo                        |
| <b>name</b>                | string       | Nome do arquivo em anexo                      |
| <b>attachment_id</b>       | string       | Identificador do anexo no servidor de e-mail  |
| <b>file</b>                | file         | Arquivo em anexo                              |

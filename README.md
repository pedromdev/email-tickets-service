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

# WebService - Marketplace & Delivery

Uma plataforma multi-lojas moderna, responsiva e profissional que conecta clientes, estabelecimentos parceiros e entregadores em um único ecossistema digital.

---

## 📸 Referências do Projeto

O projeto foi rigorosamente construído com base nas duas referências solicitadas:

1. **Imagem 1 — Modelo de Negócio (Business Model Canvas)**:
   - **Segmentos de Clientes**: Clientes em busca de comodidade e praticidade (com foco também em pessoas com mobilidade reduzida), Empresas Parceiras (restaurantes, supermercados, farmácias, moda, eletrônicos, beleza, utilidades e pet shop) e Entregadores Parceiros.
   - **Proposta de Valor**: Plataforma única para encontrar e comprar de diversas lojas com segurança; para os comerciantes, divulgação e vendas sem custos de desenvolvimento de aplicativo próprio.
   - **Canais**: Web Service responsivo (Desktop, Tablet e Mobile), redes sociais e marketing digital.
   - **Relacionamento com Clientes**: Atendimento via chat e suporte integrado, avaliações reais por estrelas, promoções com cupons de desconto (`WEBSERVICE10`).
   - **Fontes de Receita**: Comissão configurável sobre cada venda (3,5% a 8%), taxas de entrega e planos de assinatura para empresas parceiras (Essencial, Pro e Corporativo).
   - **Como Funciona (3 Passos)**:
     1. Cliente escolhe o produto e realiza o pagamento seguro.
     2. Empresa parceira recebe, confirma e prepara o pedido.
     3. Entregador retira no estabelecimento e entrega no endereço informado.

2. **Imagem 2 — Identidade Visual e Estética TechPro**:
   - **Paleta de Cores de Alta Tecnologia**:
     - Fundo Primário: `#030A12` (azul-marinho profundo/quase preto)
     - Fundo Secundário: `#071522`
     - Fundo dos Cards: `#0B1B2A` com bordas sutis `#162C46`
     - Azul Ciano Elétrico (Destaque Principal): `#39BDF5`
     - Azul Tecnológico: `#087BD4`
     - Ciano Claro de Contraste: `#A8E1FF`
     - Tipografia Clara: `#F5F9FF` e `#B8C7D8`
   - **Elementos de Design Replicados**:
     - Cabeçalho fixo com logotipo `</> WebService - Marketplace & Delivery`, atalhos de navegação com traço azul luminoso interativo (`nav-item-link`) ativo no item atual e em hover, barra de pesquisa rápida e botões arredondados em ciano elétrico.
     - Seção Hero impactante com tag *"COMODIDADE NA PALMA DA SUA MÃO"*, manchete com destaque luminoso *"em um só lugar"*, botões pill e ilustração tecnológica.
     - **Faixa de Benefícios Horizontal (Tech Strip)** diretamente inspirada na barra contrastante da Imagem 2 (fundo ciano-claro com ícones e textos escuros de altíssimo contraste para: *Praticidade*, *Segurança*, *Agilidade* e *Variedade*).
     - Cards arredondados com efeito hover luminoso (`glow-card`), sombras neon suaves e transições elegantes.

---

## 🚀 Funcionalidades Interativas Implementadas

- **Navegação Completa & Responsiva**: Menu fixo no topo com transição de vidro (`backdrop-blur`), menu drawer para dispositivos móveis e atalhos rápidos.
- **Alternador de Papéis do Canvas (Role Switcher Demo)**:
  - **Visão Cliente**: O marketplace completo com catálogo, categorias e compras.
  - **Visão Empresa Parceira (Painel do Lojista)**: Demonstração do dashboard da loja com faturamento diário (R$ 1.480,50), comissão retida (5%) e fila de pedidos em preparo na cozinha.
  - **Visão Entregador (App do Entregador)**: Visualização de ganhos do dia (R$ 138,50), rotas disponíveis, valor líquido da corrida e botão para aceitar pedidos.
- **Vitrine e Busca em Tempo Real**:
  - Filtros instantâneos por categoria (Alimentação, Mercados, Farmácias, Eletrônicos, Moda, Beleza, Casa, Pet Shop).
  - Busca dinâmica de produtos e lojas conforme a digitação.
  - Filtro por faixas de preço (Até R$ 50, R$ 50 a R$ 150, Acima de R$ 150).
- **Carrinho de Compras Interativo (Slide-over Drawer)**:
  - Adição de produtos com feedback via Toast.
  - Controle de quantidade (`+` / `-`), exclusão de itens e cálculo automático de subtotal.
  - **Suporte a Cupons de Desconto**: Digite `WEBSERVICE10` e ganhe R$ 10 de desconto imediato.
  - Regra de frete dinâmico: Grátis acima de R$ 100,00 ou taxa fixa calculada.
- **Fluxo de Checkout Completo (3 Etapas)**:
  - **Etapa 1: Endereço Brasileiro**: Nome, telefone, CEP com preenchimento automático de logradouro, bairro, cidade e UF.
  - **Etapa 2: Formas de Pagamento**: Pix com chave "Copia e Cola" e QR Code em SVG dinâmico, Cartão de Crédito com parcelamento ou Cartão de Débito.
  - **Etapa 3: Revisão e Confirmação**: Geração de código de pedido único no formato `#CON-XXXXXX`.
- **Rastreador de Pedidos em Tempo Real (Live Stepper)**:
  - Acompanhe o pedido em 4 estágios: *Confirmado* ➔ *Em Preparo* ➔ *Entregador a Caminho* ➔ *Entregue*.
  - Informações do entregador alocado (nome, avaliação, modelo da moto e placa).
  - Botão interativo para simular o avanço do entregador em tempo real.
- **Modal de Visualização Rápida de Produto (Quick View)**:
  - Clique em qualquer produto para ver detalhes expandidos, avaliações, loja parceira e seletor de quantidade.
- **Formulários com Validação**:
  - Cadastro de Clientes e Login com recuperação de senha.
  - Cadastro de Novas Empresas Parceiras com escolha de plano.
  - Cadastro de Entregadores Parceiros (com seleção de veículo: moto, bike, carro).
  - Formulário de Contato Direto e Inscrição na Newsletter.
- **Assistente Virtual de Suporte (Chat Widget)**:
  - Botão flutuante no canto inferior direito com pulso de status online.
  - Janela de atendimento com respostas sobre rastreamento, cadastro de lojas, entregadores e formas de pagamento, sem repetir a resposta padrão.
- **Painel de Lojas e Produtos**:
  - Acesse em **Painel Loja** para cadastrar/remover lojas, adicionar produtos com preço, descrição e imagem, e remover itens.
  - O catálogo de demonstração apresenta produtos e serviços com imagens próprias, sem gerar itens de preenchimento que repitam a foto de outro produto. Inclui iFood, Mercado Livre, açaí, padaria, pizzaria, hortifruti, farmácia, livraria, floricultura, pet shop, moda esportiva, doceria, salão, barbearia e assistência técnica de computadores.
  - Serviços de barbearia, salão e assistência técnica permitem selecionar data e horário; os agendamentos aparecem em **Meus Agendamentos** e são mantidos após a confirmação do pedido.
  - As alterações são salvas no armazenamento local do navegador; lojas e produtos de demonstração novos são incluídos também em catálogos já salvos.
- **Meus Agendamentos**: Consulte serviços no carrinho e agendamentos confirmados, com estabelecimento, data, horário e número do pedido. Os agendamentos confirmados ficam salvos neste navegador.

---

## 🛠️ Tecnologias Utilizadas

- **HTML5 Semântico**: Estruturação acessível e otimizada para SEO.
- **Tailwind CSS (v3.4)**: Estilização moderna e utilitária com design system escuro e responsivo.
- **CSS Customizado (`styles.css`)**: Efeitos de brilho ciano (`glow-cyan`, `glow-card`), tipografia Google Fonts (*Inter*, *Outfit* e *Caveat*), animações de flutuação e sistema de toasts.
- **JavaScript Moderno ES6+ (`app.js` & `data.js`)**: Gerenciamento de estado reativo, renderização dinâmica, manipulação do carrinho e modais sem dependência de ferramentas de build complexas.
- **Lucide Icons**: Biblioteca de ícones vetoriais leves e minimalistas.

---

## 💻 Como Executar o Projeto

Como o projeto foi projetado com arquitetura web moderna e independente de servidores proprietários:

1. **Opção Direta (Mais simples)**:
   - Dê um duplo clique no arquivo `index.html` em qualquer computador com Windows, macOS ou Linux.
   - O site abrirá imediatamente em qualquer navegador moderno (Edge, Chrome, Opera, Firefox, Safari).

2. **Opção via Terminal (PowerShell)**:
   ```powershell
   Start-Process index.html
   ```

3. **Opção com Servidor Local (se desejar)**:
   Caso possua alguma extensão como Live Server no VS Code ou equivalente, basta iniciar sobre a pasta do projeto.

---

## 🇧🇷 Idioma e Localização

- **100% em Português do Brasil (pt-BR)**: Nenhum texto em inglês ou Lorem Ipsum residual.
- **Moeda**: Formatada no padrão brasileiro (`R$ 00,00`).
- **Endereços**: Campos de CEP (com busca simulada), Rua, Número, Complemento, Bairro, Cidade e Estado (UF).
- **Pagamentos**: Inclusão destacada do Pix (padrão do Banco Central do Brasil).

// ==========================================================================
// BANCO DE DADOS DEMONSTRATIVO - WEBSERVICE MARKETPLACE
// Baseado no Modelo de Negócio (Imagem 1) e Design TechPro (Imagem 2)
// Idioma: Português do Brasil (pt-BR)
// ==========================================================================

const MARKETPLACE_DATA = {
  // Informações da Marca e Contato
  brand: {
    name: "WebService",
    tagline: "Uma plataforma, várias empresas, infinitas possibilidades.",
    heroSubtitle: "Todas as suas compras em um só lugar.",
    description: "O ecossistema digital inteligente WebService que conecta clientes, estabelecimentos comerciais locais e entregadores autônomos em uma experiência ágil, segura e prática.",
    supportEmail: "contato@webservice.com.br",
    supportPhone: "(11) 4003-8920",
    supportWhatsapp: "(11) 98765-4321",
    address: "Av. Paulista, 1842 - Bela Vista, São Paulo - SP, 01310-200",
    cnpj: "48.291.503/0001-89"
  },

  // 8 Categorias Interativas
  categories: [
    {
      id: "alimentacao",
      name: "Restaurantes & Alimentação",
      shortName: "Restaurantes",
      icon: "utensils",
      itemCount: 420,
      description: "Pratos executivos, hambúrgueres artesanais, pizzas e delícias locais.",
      gradient: "from-amber-500/20 to-orange-500/10"
    },
    {
      id: "supermercados",
      name: "Supermercados & Mercearia",
      shortName: "Mercados",
      icon: "shopping-cart",
      itemCount: 890,
      description: "Hortifruti fresco, despensa completa, bebidas e itens do dia a dia.",
      gradient: "from-emerald-500/20 to-teal-500/10"
    },
    {
      id: "farmacias",
      name: "Farmácias & Saúde",
      shortName: "Farmácias",
      icon: "heart-pulse",
      itemCount: 310,
      description: "Medicamentos, dermocosméticos, vitaminas e produtos de primeiros socorros.",
      gradient: "from-rose-500/20 to-pink-500/10"
    },
    {
      id: "roupas",
      name: "Roupas & Acessórios",
      shortName: "Moda",
      icon: "shirt",
      itemCount: 560,
      description: "Tendências em vestuário, calçados e acessórios das melhores boutiques.",
      gradient: "from-purple-500/20 to-indigo-500/10"
    },
    {
      id: "eletronicos",
      name: "Eletrônicos & Tecnologia",
      shortName: "Eletrônicos",
      icon: "laptop",
      itemCount: 240,
      description: "Smartphones, fones sem fio, periféricos e acessórios tecnológicos.",
      gradient: "from-cyan-500/20 to-blue-500/10"
    },
    {
      id: "beleza",
      name: "Beleza & Cosméticos",
      shortName: "Beleza",
      icon: "sparkles",
      itemCount: 380,
      description: "Skincare, perfumaria importada e nacional, cuidados capilares e make.",
      gradient: "from-fuchsia-500/20 to-pink-500/10"
    },
    {
      id: "casa",
      name: "Casa & Utilidades",
      shortName: "Casa",
      icon: "home",
      itemCount: 195,
      description: "Decoração moderna, utilidades para o lar, organizadores e conforto.",
      gradient: "from-blue-500/20 to-sky-500/10"
    },
    {
      id: "outros",
      name: "Pet Shop & Outros",
      shortName: "Pet Shop",
      icon: "paw-print",
      itemCount: 275,
      description: "Rações selecionadas, petiscos, brinquedos e cuidados para seu pet.",
      gradient: "from-yellow-500/20 to-amber-500/10"
    }
  ],

  // Empresas Parceiras em Destaque
  businesses: [
    {
      id: "bistro-artesanal",
      name: "Bistrô & Burger Artesanal",
      category: "Restaurantes",
      categoryId: "alimentacao",
      rating: 4.9,
      reviewsCount: 382,
      deliveryTime: "25 - 35 min",
      deliveryFee: 5.90,
      image: "https://images.unsplash.com/photo-1550547660-d9450f859349?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=150&auto=format&fit=crop&q=80",
      badge: "Super Parceiro",
      neighborhood: "Jardins, São Paulo",
      description: "Hambúrgueres defumados em lenha nobre, batatas rústicas e molhos autorais."
    },
    {
      id: "emporio-verde",
      name: "Empório Verde & Mercado",
      category: "Supermercados",
      categoryId: "supermercados",
      rating: 4.8,
      reviewsCount: 512,
      deliveryTime: "30 - 45 min",
      deliveryFee: 7.50,
      image: "https://images.unsplash.com/photo-1542838132-92c53300491e?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1607344645866-009c320c5ab8?w=150&auto=format&fit=crop&q=80",
      badge: "Hortifruti do Dia",
      neighborhood: "Pinheiros, São Paulo",
      description: "Produtos orgânicos selecionados, vinhos finos, pães artesanais e mercearia."
    },
    {
      id: "drogaria-vital",
      name: "Drogaria Vital & Bem-Estar",
      category: "Farmácias",
      categoryId: "farmacias",
      rating: 4.9,
      reviewsCount: 840,
      deliveryTime: "15 - 25 min",
      deliveryFee: 0.00,
      image: "https://images.unsplash.com/photo-1586015555751-63bb77f4322a?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1576602976047-174e57a47881?w=150&auto=format&fit=crop&q=80",
      badge: "Frete Grátis",
      neighborhood: "Bela Vista, São Paulo",
      description: "Atendimento farmacêutico de prontidão, entrega expressa de medicamentos e vitaminas."
    },
    {
      id: "techvolt",
      name: "TechVolt Eletrônicos",
      category: "Eletrônicos",
      categoryId: "eletronicos",
      rating: 4.9,
      reviewsCount: 290,
      deliveryTime: "35 - 50 min",
      deliveryFee: 0.00,
      image: "https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=150&auto=format&fit=crop&q=80",
      badge: "Garantia 1 Ano",
      neighborhood: "Vila Olímpia, São Paulo",
      description: "Periféricos gamer, fones bluetooth de alta fidelidade e acessórios premium para setup."
    },
    {
      id: "urbana-chic",
      name: "Ateliê Moda Urbana",
      category: "Moda",
      categoryId: "roupas",
      rating: 4.7,
      reviewsCount: 178,
      deliveryTime: "40 - 60 min",
      deliveryFee: 8.90,
      image: "https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=150&auto=format&fit=crop&q=80",
      badge: "Nova Coleção",
      neighborhood: "Moema, São Paulo",
      description: "Roupas contemporâneas com design autoral brasileiro e tecidos sustentáveis."
    },
    {
      id: "glow-cosmeticos",
      name: "Glow & Essência Cosméticos",
      category: "Beleza",
      categoryId: "beleza",
      rating: 4.9,
      reviewsCount: 425,
      deliveryTime: "25 - 40 min",
      deliveryFee: 4.90,
      image: "https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=150&auto=format&fit=crop&q=80",
      badge: "Cruelty Free",
      neighborhood: "Itaim Bibi, São Paulo",
      description: "Marcas consagradas de skincare, maquiagens hipoalergênicas e perfumaria fina."
    },
    {
      id: "zen-casa",
      name: "Zen Casa & Design",
      category: "Casa",
      categoryId: "casa",
      rating: 4.8,
      reviewsCount: 142,
      deliveryTime: "30 - 50 min",
      deliveryFee: 6.90,
      image: "https://images.unsplash.com/photo-1513519245088-0e12902e5a38?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=150&auto=format&fit=crop&q=80",
      badge: "Design Exclusivo",
      neighborhood: "Perdizes, São Paulo",
      description: "Peças decorativas em cerâmica, iluminação aconchegante e organizadores inteligentes."
    },
    {
      id: "patinhas-pet",
      name: "Patinhas Pet & Cia",
      category: "Pet Shop",
      categoryId: "outros",
      rating: 4.9,
      reviewsCount: 310,
      deliveryTime: "20 - 35 min",
      deliveryFee: 5.00,
      image: "https://images.unsplash.com/photo-1548767797-d8c844163c4c?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=150&auto=format&fit=crop&q=80",
      badge: "Mais Vendidos",
      neighborhood: "Brooklin, São Paulo",
      description: "Rações Super Premium, petiscos naturais desidratados e brinquedos interativos."
    }
  ],

  // Produtos em Destaque no Marketplace
  products: [
    {
      id: "prod-1",
      name: "Burger Artesanal Black Angus 200g",
      businessName: "Bistrô & Burger Artesanal",
      businessId: "bistro-artesanal",
      categoryId: "alimentacao",
      categoryName: "Alimentação",
      price: 46.90,
      originalPrice: 54.90,
      rating: 4.9,
      image: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&auto=format&fit=crop&q=80",
      badge: "Mais Vendido",
      description: "Pão brioche tostado na manteiga de garrafa, blend nobre de Black Angus 200g, cheddar inglês derretido, cebola caramelizada e bacon crocante."
    },
    {
      id: "prod-2",
      name: "Cesta de Frutas & Orgânicos da Estação",
      businessName: "Empório Verde & Mercado",
      businessId: "emporio-verde",
      categoryId: "supermercados",
      categoryName: "Supermercados",
      price: 68.00,
      originalPrice: 79.90,
      rating: 4.8,
      image: "https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=600&auto=format&fit=crop&q=80",
      badge: "15% OFF",
      description: "Mix completo com maçãs Fuji, bananas prata, uvas sem semente, mangas tommy e morangos frescos direto de produtores familiares."
    },
    {
      id: "prod-3",
      name: "Kit Multivitamínico + Vitamina C 1000mg",
      businessName: "Drogaria Vital & Bem-Estar",
      businessId: "drogaria-vital",
      categoryId: "farmacias",
      categoryName: "Farmácias",
      price: 52.90,
      originalPrice: 65.00,
      rating: 4.9,
      image: "https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&auto=format&fit=crop&q=80",
      badge: "Saúde & Imunidade",
      description: "Fórmula concentrada para suporte imunológico diário com zinco quelato, vitamina D3 e complexo B completo. Embalagem com 60 cápsulas."
    },
    {
      id: "prod-4",
      name: "Fone Bluetooth Cancelamento Ativo de Ruído",
      businessName: "TechVolt Eletrônicos",
      businessId: "techvolt",
      categoryId: "eletronicos",
      categoryName: "Eletrônicos",
      price: 249.90,
      originalPrice: 320.00,
      rating: 4.9,
      image: "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80",
      badge: "Frete Grátis",
      description: "Tecnologia ANC híbrida, até 35 horas de reprodução contínua, graves potentes de 40mm e microfones com redução de ruído para chamadas cristalinas."
    },
    {
      id: "prod-5",
      name: "Jaqueta Corta-Vento Minimalista Tech",
      businessName: "Ateliê Moda Urbana",
      businessId: "urbana-chic",
      categoryId: "roupas",
      categoryName: "Moda",
      price: 189.00,
      originalPrice: 220.00,
      rating: 4.7,
      image: "https://images.unsplash.com/photo-1544441893-675973e31985?w=600&auto=format&fit=crop&q=80",
      badge: "Coleção 2026",
      description: "Tecido impermeável ultraleve, capuz ajustável e bolsos com zíper selado. Perfeita para quem vive o ritmo dinâmico da cidade."
    },
    {
      id: "prod-6",
      name: "Sérum Facial Ácido Hialurônico Puro + Niacinamida",
      businessName: "Glow & Essência Cosméticos",
      businessId: "glow-cosmeticos",
      categoryId: "beleza",
      categoryName: "Beleza",
      price: 89.90,
      originalPrice: 110.00,
      rating: 4.9,
      image: "https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=600&auto=format&fit=crop&q=80",
      badge: "Destaque Beleza",
      description: "Hidratação profunda sem oleosidade, restaura a luminosidade natural e uniformiza o tom da pele em 14 dias de uso contínuo."
    },
    {
      id: "prod-7",
      name: "Luminária Inteligente LED RGB Desk Pro",
      businessName: "Zen Casa & Design",
      businessId: "zen-casa",
      categoryId: "casa",
      categoryName: "Casa",
      price: 139.90,
      originalPrice: 175.00,
      rating: 4.8,
      image: "https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=600&auto=format&fit=crop&q=80",
      badge: "Setup Tech",
      description: "Controle por toque e smartphone, sincronização com música, mais de 16 milhões de cores e modo de leitura com luz quente calmante."
    },
    {
      id: "prod-8",
      name: "Ração Super Premium Cães Adultos 10kg",
      businessName: "Patinhas Pet & Cia",
      businessId: "patinhas-pet",
      categoryId: "outros",
      categoryName: "Pet Shop",
      price: 174.90,
      originalPrice: 199.90,
      rating: 4.9,
      image: "https://images.unsplash.com/photo-1568640347023-a616a30bc3bd?w=600&auto=format&fit=crop&q=80",
      badge: "Entrega Rápida",
      description: "Sem corantes ou aromatizantes artificiais, rica em ômega 3 e 6 para pelos brilhantes e condroitina para articulações fortes."
    },
    {
      id: "prod-9",
      name: "Combo Pizza Artesanal Margherita & Guaraná 2L",
      businessName: "Bistrô & Burger Artesanal",
      businessId: "bistro-artesanal",
      categoryId: "alimentacao",
      categoryName: "Alimentação",
      price: 64.90,
      originalPrice: 75.00,
      rating: 4.9,
      image: "https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600&auto=format&fit=crop&q=80",
      badge: "Combo Especial",
      description: "Massa de fermentação lenta por 48h, molho de tomate pelado italiano, mozzarella de búfala e folhas frescas de manjericão."
    },
    {
      id: "prod-10",
      name: "Café Especial Moído em Grãos Selecionados 500g",
      businessName: "Empório Verde & Mercado",
      businessId: "emporio-verde",
      categoryId: "supermercados",
      categoryName: "Supermercados",
      price: 36.50,
      originalPrice: 42.00,
      rating: 4.9,
      image: "https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=600&auto=format&fit=crop&q=80",
      badge: "86 Pontos SCA",
      description: "100% Arábica da região da Mantiqueira de Minas. Notas aromáticas de chocolate meio amargo, caramelo e acidez cítrica agradável."
    },
    {
      id: "prod-11",
      name: "Oxímetro Digital de Dedo com Display OLED",
      businessName: "Drogaria Vital & Bem-Estar",
      businessId: "drogaria-vital",
      categoryId: "farmacias",
      categoryName: "Farmácias",
      price: 69.90,
      originalPrice: 89.00,
      rating: 4.8,
      image: "https://images.unsplash.com/photo-1584017911766-d451b3d0e843?w=600&auto=format&fit=crop&q=80",
      badge: "Certificado Anvisa",
      description: "Mede oxigenação sanguínea (SpO2) e frequência cardíaca em menos de 8 segundos com alta precisão e baixo consumo de pilhas."
    },
    {
      id: "prod-12",
      name: "Teclado Mecânico Sem Fio RGB Hot-Swappable",
      businessName: "TechVolt Eletrônicos",
      businessId: "techvolt",
      categoryId: "eletronicos",
      categoryName: "Eletrônicos",
      price: 319.00,
      originalPrice: 389.00,
      rating: 5.0,
      image: "https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&auto=format&fit=crop&q=80",
      badge: "Top Gamer",
      description: "Layout compacto 75%, switches pré-lubrificados silenciosos, conectividade Bluetooth 5.2, Wireless 2.4G e cabo USB-C destacável."
    }
  ],

  // Planos de Monetização para Empresas Parceiras (Canvas: Fontes de Receita)
  partnerPlans: [
    {
      id: "basico",
      name: "Plano Essencial",
      tagline: "Ideal para comerciantes que estão iniciando as vendas online.",
      commission: "8% por pedido concluído",
      price: 0,
      priceLabel: "Sem mensalidade fixa",
      badge: "Início Sem Risco",
      features: [
        "Catálogo de até 100 produtos",
        "Acesso ao painel web de pedidos",
        "Rede de entregadores conectados",
        "Recebimento quinzenal automático",
        "Suporte padrão via e-mail e chat"
      ],
      isPopular: false,
      cta: "Começar no Essencial"
    },
    {
      id: "pro",
      name: "Plano Crescimento Pro",
      tagline: "Para empresas que buscam aceleração de vendas e destaque máximo.",
      commission: "5% por pedido concluído",
      price: 79.90,
      priceLabel: "/mês",
      badge: "Mais Recomendado",
      features: [
        "Catálogo ilimitado de produtos",
        "Posicionamento prioritário nas buscas",
        "Emissão de cupons de desconto próprios",
        "Dashboard analítico de vendas e clientes",
        "Repasse semanal antecipado",
        "Suporte dedicado via WhatsApp"
      ],
      isPopular: true,
      cta: "Quero o Plano Pro"
    },
    {
      id: "enterprise",
      name: "Plano Corporativo & Redes",
      tagline: "Para franquias, redes e grandes estabelecimentos.",
      commission: "3,5% por pedido concluído",
      price: 199.00,
      priceLabel: "/mês",
      badge: "Máxima Visibilidade",
      features: [
        "Multi-filiais em um único login",
        "Banner promocional rotativo na Home",
        "Integração via API com seu PDV/ERP",
        "Gerente de conta exclusivo",
        "Treinamento de equipe e onboarding VIP",
        "Taxa de entrega subsidiada para clientes"
      ],
      isPopular: false,
      cta: "Falar com Consultor"
    }
  ],

  // Perguntas Frequentes (FAQ)
  faq: [
    {
      question: "Como funciona a entrega e qual o prazo médio?",
      answer: "Ao fazer um pedido, o estabelecimento parceiro mais próximo recebe a notificação instantaneamente e inicia a separação. Um entregador parceiro da nossa rede retira o pacote e leva diretamente até o seu endereço. O tempo médio varia de 25 a 45 minutos dependendo da sua localidade."
    },
    {
      question: "O WebService é adaptado para pessoas com deficiência ou mobilidade reduzida?",
      answer: "Sim! Desenvolvemos a plataforma com total acessibilidade digital (navegação por teclado, alto contraste, leitor de tela) para que clientes que não podem ou preferem não sair de casa tenham autonomia plena para receber mantimentos, remédios e refeições na porta."
    },
    {
      question: "Quais são as opções de pagamento seguras aceitas?",
      answer: "Você pode pagar diretamente pela plataforma via Pix (com aprovação imediata em segundos), Cartão de Crédito (com parcelamento em compras elegíveis) e Cartão de Débito, através de gateways certificados com criptografia de ponta a ponta."
    },
    {
      question: "Como minha empresa pode se tornar parceira do WebService?",
      answer: "Basta clicar em 'Quero ser Parceiro' e preencher o formulário inicial com CNPJ e dados de contato. Nossa equipe faz a validação em até 24 horas úteis e disponibiliza o painel para você cadastrar seus produtos e começar a faturar."
    },
    {
      question: "Como funciona para quem deseja trabalhar como entregador?",
      answer: "Entregadores autônomos com moto, bicicleta ou carro podem se cadastrar pelo botão 'Cadastre-se como Entregador'. Após o envio dos documentos básicos (CNH ou RG), você tem acesso aos pedidos disponíveis com rotas claras e repasses seguros."
    },
    {
      question: "Como acompanho o status da minha compra em tempo real?",
      answer: "Logo após a confirmação do pagamento, você visualiza a tela de rastreamento com atualização ao vivo dos 3 estágios: Preparando no Estabelecimento, Entregador em Trânsito e Pedido Concluído."
    }
  ],

  // Depoimentos / Avaliações reais de clientes e parceiros
  testimonials: [
    {
      name: "Mariana Alencar",
      role: "Cliente - São Paulo/SP",
      avatar: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80",
      content: "Como trabalho home office e tenho mobilidade reduzida por conta de uma cirurgia, o WebService salvou minha rotina. Peço comida, farmácia e mercado no mesmo aplicativo!",
      rating: 5
    },
    {
      name: "Carlos Eduardo Mendes",
      role: "Proprietário do Bistrô & Burger",
      avatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80",
      content: "Aumentamos nosso faturamento em 38% no primeiro mês. O painel é facílimo de usar e não precisamos gastar fortuna desenvolvendo aplicativo próprio.",
      rating: 5
    },
    {
      name: "Rodrigo Santos",
      role: "Entregador Parceiro",
      avatar: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80",
      content: "Aplicativo leve e direto ao ponto. Vejo o valor exato da rota antes de aceitar e o suporte é sempre ágil quando preciso de ajuda nas entregas.",
      rating: 5
    }
  ]
};

// Exporta globalmente para uso direto no navegador sem necessidade de build tool
window.MARKETPLACE_DATA = MARKETPLACE_DATA;

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
    },
    {
      id: "ifood-demo",
      name: "iFood",
      category: "Restaurantes",
      categoryId: "alimentacao",
      rating: 4.9,
      reviewsCount: 0,
      deliveryTime: "25 - 40 min",
      deliveryFee: 0,
      image: "https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=150&auto=format&fit=crop&q=80",
      badge: "Loja demonstrativa",
      neighborhood: "Centro, São Paulo",
      description: "Loja de demonstração de refeições e combos para pedidos online."
    },
    {
      id: "mercado-livre-demo",
      name: "Mercado Livre",
      category: "Eletrônicos",
      categoryId: "eletronicos",
      rating: 4.8,
      reviewsCount: 0,
      deliveryTime: "30 - 50 min",
      deliveryFee: 0,
      image: "https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=150&auto=format&fit=crop&q=80",
      badge: "Loja demonstrativa",
      neighborhood: "Centro, São Paulo",
      description: "Vitrine de demonstração para eletrônicos e acessórios."
    },
    {
      id: "acai-da-praca",
      name: "Açaí da Praça",
      category: "Alimentação",
      categoryId: "alimentacao",
      rating: 4.9,
      reviewsCount: 0,
      deliveryTime: "20 - 30 min",
      deliveryFee: 5.9,
      image: "acai-bowl.svg",
      logo: "acai-bowl.svg",
      badge: "Açaí e smoothies",
      neighborhood: "Vila Mariana, São Paulo",
      description: "Açaí batido na hora com frutas, granola e acompanhamentos."
    },
    {
      id: "barbearia-navalha",
      name: "Barbearia Navalha Urbana",
      category: "Beleza",
      categoryId: "beleza",
      rating: 4.9,
      reviewsCount: 0,
      deliveryTime: "Agendamento",
      deliveryFee: 0,
      image: "https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=150&auto=format&fit=crop&q=80",
      badge: "Agende seu horário",
      neighborhood: "Pinheiros, São Paulo",
      description: "Cortes masculinos, barba e cuidados pessoais com hora marcada.",
      appointmentBased: true
    },
    {
      id: "business-demo-padaria",
      name: "Padaria Pão da Vila",
      category: "Padarias",
      categoryId: "alimentacao",
      rating: 4.8,
      reviewsCount: 215,
      deliveryTime: "20 - 35 min",
      deliveryFee: 4.9,
      image: "https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1509440159596-0249088772ff?w=150&auto=format&fit=crop&q=80",
      badge: "Pão quentinho",
      neighborhood: "Vila Mariana, São Paulo",
      description: "Pães artesanais, bolos caseiros e café fresquinho todos os dias."
    },
    {
      id: "business-demo-pizzaria",
      name: "Pizzaria Forno Artesanal",
      category: "Pizzarias",
      categoryId: "alimentacao",
      rating: 4.9,
      reviewsCount: 188,
      deliveryTime: "30 - 45 min",
      deliveryFee: 6.9,
      image: "https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1513104890138-7c749659a591?w=150&auto=format&fit=crop&q=80",
      badge: "Massa de longa fermentação",
      neighborhood: "Moema, São Paulo",
      description: "Pizzas artesanais assadas no forno com ingredientes selecionados."
    },
    {
      id: "business-demo-hortifruti",
      name: "Hortifruti da Estação",
      category: "Hortifruti",
      categoryId: "supermercados",
      rating: 4.8,
      reviewsCount: 164,
      deliveryTime: "25 - 40 min",
      deliveryFee: 5.5,
      image: "https://images.unsplash.com/photo-1542838132-92c53300491e?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1542838132-92c53300491e?w=150&auto=format&fit=crop&q=80",
      badge: "Fresquinho hoje",
      neighborhood: "Pinheiros, São Paulo",
      description: "Frutas, legumes e verduras selecionados de produtores locais."
    },
    {
      id: "business-demo-farmacia",
      name: "Farmácia Bem Viver",
      category: "Farmácias",
      categoryId: "farmacias",
      rating: 4.7,
      reviewsCount: 132,
      deliveryTime: "20 - 35 min",
      deliveryFee: 4.5,
      image: "https://images.unsplash.com/photo-1586015555751-63bb77f4322a?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1576602976047-174e57a47881?w=150&auto=format&fit=crop&q=80",
      badge: "Bem-estar",
      neighborhood: "Aclimação, São Paulo",
      description: "Cuidados pessoais, higiene e itens essenciais para o dia a dia."
    },
    {
      id: "business-demo-livraria",
      name: "Livraria Horizonte",
      category: "Livrarias",
      categoryId: "outros",
      rating: 4.9,
      reviewsCount: 96,
      deliveryTime: "30 - 50 min",
      deliveryFee: 6,
      image: "https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=150&auto=format&fit=crop&q=80",
      badge: "Leitura para todos",
      neighborhood: "Consolação, São Paulo",
      description: "Livros de ficção, literatura infantil e títulos para aprender e explorar."
    },
    {
      id: "business-demo-floricultura",
      name: "Floricultura Primavera",
      category: "Flores e Presentes",
      categoryId: "casa",
      rating: 4.9,
      reviewsCount: 118,
      deliveryTime: "25 - 45 min",
      deliveryFee: 7.5,
      image: "https://images.unsplash.com/photo-1490750967868-88aa4486c946?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1490750967868-88aa4486c946?w=150&auto=format&fit=crop&q=80",
      badge: "Feito com carinho",
      neighborhood: "Jardins, São Paulo",
      description: "Buquês, plantas e presentes para celebrar momentos especiais."
    },
    {
      id: "business-demo-pet",
      name: "Mundo Animal Pet Shop",
      category: "Pet Shop",
      categoryId: "outros",
      rating: 4.8,
      reviewsCount: 143,
      deliveryTime: "20 - 35 min",
      deliveryFee: 5,
      image: "https://images.unsplash.com/photo-1548767797-d8c844163c4c?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=150&auto=format&fit=crop&q=80",
      badge: "Amor pelos pets",
      neighborhood: "Brooklin, São Paulo",
      description: "Rações, brinquedos e acessórios para cães e gatos."
    },
    {
      id: "business-demo-esportes",
      name: "Movimento Esportes",
      category: "Moda Esportiva",
      categoryId: "roupas",
      rating: 4.7,
      reviewsCount: 87,
      deliveryTime: "35 - 50 min",
      deliveryFee: 8,
      image: "https://images.unsplash.com/photo-1538805060514-97d9cc17730c?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=150&auto=format&fit=crop&q=80",
      badge: "Vista seu movimento",
      neighborhood: "Vila Olímpia, São Paulo",
      description: "Roupas e acessórios confortáveis para treino, corrida e lazer."
    },
    {
      id: "business-demo-salao",
      name: "Studio Aura Beleza",
      category: "Salão e Beleza",
      categoryId: "beleza",
      rating: 4.9,
      reviewsCount: 105,
      deliveryTime: "Agendamento",
      deliveryFee: 0,
      image: "https://images.unsplash.com/photo-1560066984-138dadb4c035?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=150&auto=format&fit=crop&q=80",
      badge: "Agende seu horário",
      neighborhood: "Itaim Bibi, São Paulo",
      description: "Serviços de cabelo e beleza com atendimento mediante agendamento.",
      appointmentBased: true
    },
    {
      id: "business-demo-doceria",
      name: "Doceria Cacau & Açúcar",
      category: "Docerias",
      categoryId: "alimentacao",
      rating: 4.9,
      reviewsCount: 127,
      deliveryTime: "25 - 40 min",
      deliveryFee: 5.9,
      image: "https://images.unsplash.com/photo-1481391319762-47dff72954d9?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1481391319762-47dff72954d9?w=150&auto=format&fit=crop&q=80",
      badge: "Doces artesanais",
      neighborhood: "Paraíso, São Paulo",
      description: "Brigadeiros, brownies e bolos preparados em pequenos lotes."
    },
    {
      id: "business-demo-bytecare",
      name: "ByteCare Assistência Técnica",
      category: "Assistência Técnica",
      categoryId: "eletronicos",
      rating: 4.9,
      reviewsCount: 74,
      deliveryTime: "Agendamento",
      deliveryFee: 0,
      image: "https://images.unsplash.com/photo-1587202372775-e229f172b9d7?w=600&auto=format&fit=crop&q=80",
      logo: "https://images.unsplash.com/photo-1587202372775-e229f172b9d7?w=150&auto=format&fit=crop&q=80",
      badge: "Agende seu atendimento",
      neighborhood: "Vila Mariana, São Paulo",
      description: "Diagnóstico, manutenção e suporte de computadores com horário agendado.",
      appointmentBased: true
    }
  ],

  // Produtos em Destaque no Marketplace
  // Cada produto do catálogo demonstrativo utiliza um arquivo de imagem exclusivo.
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
    },
    {
      id: "prod-ifood-1",
      name: "Combo de Hambúrguer e Batata",
      businessName: "iFood",
      businessId: "ifood-demo",
      categoryId: "alimentacao",
      categoryName: "Alimentação",
      price: 39.9,
      rating: 4.9,
      image: "https://images.unsplash.com/photo-1550547660-d9450f859349?w=600&auto=format&fit=crop&q=80",
      badge: "Loja demonstrativa",
      description: "Combo demonstrativo com hambúrguer artesanal e batatas crocantes."
    },
    {
      id: "prod-mercado-livre-1",
      name: "Fone Bluetooth",
      businessName: "Mercado Livre",
      businessId: "mercado-livre-demo",
      categoryId: "eletronicos",
      categoryName: "Eletrônicos",
      price: 89.9,
      rating: 4.8,
      image: "https://images.unsplash.com/photo-1484704849700-f032a568e944?w=600&auto=format&fit=crop&q=80",
      badge: "Loja demonstrativa",
      description: "Fone sem fio de demonstração com estojo de carregamento."
    },
    {
      id: "prod-acai-1",
      name: "Açaí Tradicional 500 ml",
      businessName: "Açaí da Praça",
      businessId: "acai-da-praca",
      categoryId: "alimentacao",
      categoryName: "Alimentação",
      price: 24.9,
      rating: 4.9,
      image: "acai-bowl.svg",
      badge: "Mais pedido",
      description: "Açaí cremoso de 500 ml com banana e granola."
    },
    {
      id: "prod-barbearia-1",
      name: "Corte masculino",
      businessName: "Barbearia Navalha Urbana",
      businessId: "barbearia-navalha",
      categoryId: "beleza",
      categoryName: "Beleza",
      price: 45,
      rating: 4.9,
      image: "https://images.unsplash.com/photo-1621607512214-68297480165e?w=600&auto=format&fit=crop&q=80",
      badge: "Agendamento",
      description: "Serviço de corte masculino com agendamento na barbearia."
    },
    {
      id: "prod-demo-padaria-1",
      name: "Pão de queijo artesanal (6 unidades)",
      businessName: "Padaria Pão da Vila",
      businessId: "business-demo-padaria",
      categoryId: "alimentacao",
      categoryName: "Alimentação",
      price: 18.9,
      rating: 4.8,
      image: "https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=600&auto=format&fit=crop&q=80",
      badge: "Saiu do forno",
      description: "Pães de queijo assados no dia, crocantes por fora e macios por dentro."
    },
    {
      id: "prod-demo-padaria-2",
      name: "Bolo caseiro de cenoura",
      businessName: "Padaria Pão da Vila",
      businessId: "business-demo-padaria",
      categoryId: "alimentacao",
      categoryName: "Alimentação",
      price: 29.9,
      rating: 4.8,
      image: "https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&auto=format&fit=crop&q=80",
      badge: "Feito hoje",
      description: "Bolo fofinho de cenoura com cobertura de chocolate. Tamanho família."
    },
    {
      id: "prod-demo-pizzaria-1",
      name: "Pizza Margherita artesanal",
      businessName: "Pizzaria Forno Artesanal",
      businessId: "business-demo-pizzaria",
      categoryId: "alimentacao",
      categoryName: "Alimentação",
      price: 49.9,
      rating: 4.9,
      image: "https://images.unsplash.com/photo-1579751626657-72bc17010498?w=600&auto=format&fit=crop&q=80",
      badge: "Favorita da casa",
      description: "Massa de longa fermentação, molho de tomate, mozzarella e manjericão."
    },
    {
      id: "prod-demo-pizzaria-2",
      name: "Pizza de calabresa",
      businessName: "Pizzaria Forno Artesanal",
      businessId: "business-demo-pizzaria",
      categoryId: "alimentacao",
      categoryName: "Alimentação",
      price: 52.9,
      rating: 4.8,
      image: "https://images.unsplash.com/photo-1593560708920-61dd98c46a4e?w=600&auto=format&fit=crop&q=80",
      badge: "Clássica",
      description: "Calabresa fatiada, cebola, mozzarella e massa assada no forno."
    },
    {
      id: "prod-demo-hortifruti-1",
      name: "Cesta de frutas da estação",
      businessName: "Hortifruti da Estação",
      businessId: "business-demo-hortifruti",
      categoryId: "supermercados",
      categoryName: "Supermercados",
      price: 42.9,
      rating: 4.8,
      image: "https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=600&auto=format&fit=crop&q=80",
      badge: "Seleção do dia",
      description: "Seleção variada de frutas frescas para compartilhar em casa."
    },
    {
      id: "prod-demo-hortifruti-2",
      name: "Kit para salada fresca",
      businessName: "Hortifruti da Estação",
      businessId: "business-demo-hortifruti",
      categoryId: "supermercados",
      categoryName: "Supermercados",
      price: 19.9,
      rating: 4.7,
      image: "https://images.unsplash.com/photo-1540420773420-3366772f4999?w=600&auto=format&fit=crop&q=80",
      badge: "Prático",
      description: "Folhas higienizadas e vegetais selecionados para montar sua salada."
    },
    {
      id: "prod-demo-farmacia-1",
      name: "Kit de cuidados pessoais",
      businessName: "Farmácia Bem Viver",
      businessId: "business-demo-farmacia",
      categoryId: "farmacias",
      categoryName: "Farmácias",
      price: 34.9,
      rating: 4.7,
      image: "https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600&auto=format&fit=crop&q=80",
      badge: "Cuidados diários",
      description: "Itens básicos de higiene e cuidado pessoal para a rotina."
    },
    {
      id: "prod-demo-farmacia-2",
      name: "Protetor solar facial FPS 50",
      businessName: "Farmácia Bem Viver",
      businessId: "business-demo-farmacia",
      categoryId: "farmacias",
      categoryName: "Farmácias",
      price: 54.9,
      rating: 4.8,
      image: "https://images.unsplash.com/photo-1587854692152-cbe660dbde88?w=600&auto=format&fit=crop&q=80",
      badge: "Proteção diária",
      description: "Protetor facial para uso diário. Consulte as instruções da embalagem."
    },
    {
      id: "prod-demo-livraria-1",
      name: "Romance para ler no fim de semana",
      businessName: "Livraria Horizonte",
      businessId: "business-demo-livraria",
      categoryId: "outros",
      categoryName: "Livrarias",
      price: 49.9,
      rating: 4.9,
      image: "https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=600&auto=format&fit=crop&q=80",
      badge: "Mais lido",
      description: "Uma história envolvente para acompanhar seus momentos de descanso."
    },
    {
      id: "prod-demo-livraria-2",
      name: "Livro ilustrado infantil",
      businessName: "Livraria Horizonte",
      businessId: "business-demo-livraria",
      categoryId: "outros",
      categoryName: "Livrarias",
      price: 39.9,
      rating: 4.8,
      image: "https://images.unsplash.com/photo-1512820790803-83ca734da794?w=600&auto=format&fit=crop&q=80",
      badge: "Para pequenos leitores",
      description: "Livro ilustrado para incentivar a imaginação e a leitura em família."
    },
    {
      id: "prod-demo-floricultura-1",
      name: "Buquê colorido da estação",
      businessName: "Floricultura Primavera",
      businessId: "business-demo-floricultura",
      categoryId: "casa",
      categoryName: "Casa",
      price: 89.9,
      rating: 4.9,
      image: "https://images.unsplash.com/photo-1490750967868-88aa4486c946?w=600&auto=format&fit=crop&q=80",
      badge: "Presente especial",
      description: "Arranjo com flores da estação preparado para presentear."
    },
    {
      id: "prod-demo-floricultura-2",
      name: "Planta ornamental para ambientes",
      businessName: "Floricultura Primavera",
      businessId: "business-demo-floricultura",
      categoryId: "casa",
      categoryName: "Casa",
      price: 59.9,
      rating: 4.8,
      image: "https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=600&auto=format&fit=crop&q=80",
      badge: "Toque verde",
      description: "Planta ornamental para dar mais vida à decoração da sua casa."
    },
    {
      id: "prod-demo-pet-1",
      name: "Ração para cães adultos 3 kg",
      businessName: "Mundo Animal Pet Shop",
      businessId: "business-demo-pet",
      categoryId: "outros",
      categoryName: "Pet Shop",
      price: 74.9,
      rating: 4.8,
      image: "https://images.unsplash.com/photo-1558788353-f76d92427f16?w=600&auto=format&fit=crop&q=80",
      badge: "Para seu melhor amigo",
      description: "Alimento seco para cães adultos. Confira as orientações do fabricante."
    },
    {
      id: "prod-demo-pet-2",
      name: "Brinquedo interativo para gatos",
      businessName: "Mundo Animal Pet Shop",
      businessId: "business-demo-pet",
      categoryId: "outros",
      categoryName: "Pet Shop",
      price: 32.9,
      rating: 4.7,
      image: "https://images.unsplash.com/photo-1573865526739-10659fec78a5?w=600&auto=format&fit=crop&q=80",
      badge: "Diversão garantida",
      description: "Brinquedo leve para estimular a curiosidade e a atividade do seu gato."
    },
    {
      id: "prod-demo-esportes-1",
      name: "Camiseta esportiva leve",
      businessName: "Movimento Esportes",
      businessId: "business-demo-esportes",
      categoryId: "roupas",
      categoryName: "Moda",
      price: 79.9,
      rating: 4.7,
      image: "https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=600&auto=format&fit=crop&q=80",
      badge: "Conforto no treino",
      description: "Camiseta leve e confortável para treinos e atividades ao ar livre."
    },
    {
      id: "prod-demo-esportes-2",
      name: "Tênis casual esportivo",
      businessName: "Movimento Esportes",
      businessId: "business-demo-esportes",
      categoryId: "roupas",
      categoryName: "Moda",
      price: 189.9,
      rating: 4.8,
      image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=80",
      badge: "Para o dia a dia",
      description: "Tênis versátil para caminhadas leves e compromissos do dia a dia."
    },
    {
      id: "prod-demo-salao-1",
      name: "Escova e finalização",
      businessName: "Studio Aura Beleza",
      businessId: "business-demo-salao",
      categoryId: "beleza",
      categoryName: "Beleza",
      price: 65,
      rating: 4.9,
      image: "https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=600&auto=format&fit=crop&q=80",
      badge: "Agendamento",
      description: "Serviço de escova e finalização. Horário combinado diretamente com o salão."
    },
    {
      id: "prod-demo-salao-2",
      name: "Hidratação capilar",
      businessName: "Studio Aura Beleza",
      businessId: "business-demo-salao",
      categoryId: "beleza",
      categoryName: "Beleza",
      price: 95,
      rating: 4.8,
      image: "https://images.unsplash.com/photo-1527799820374-dcf8d9d4a388?w=600&auto=format&fit=crop&q=80",
      badge: "Cuidado especial",
      description: "Tratamento hidratante com agendamento no Studio Aura Beleza."
    },
    {
      id: "prod-demo-doceria-1",
      name: "Caixa com 12 brigadeiros",
      businessName: "Doceria Cacau & Açúcar",
      businessId: "business-demo-doceria",
      categoryId: "alimentacao",
      categoryName: "Alimentação",
      price: 42.9,
      rating: 4.9,
      image: "https://images.unsplash.com/photo-1551024601-bec78aea704b?w=600&auto=format&fit=crop&q=80",
      badge: "Feito à mão",
      description: "Caixa com brigadeiros artesanais para dividir ou presentear."
    },
    {
      id: "prod-demo-doceria-2",
      name: "Brownie de chocolate",
      businessName: "Doceria Cacau & Açúcar",
      businessId: "business-demo-doceria",
      categoryId: "alimentacao",
      categoryName: "Alimentação",
      price: 14.9,
      rating: 4.8,
      image: "https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=600&auto=format&fit=crop&q=80",
      badge: "Queridinho",
      description: "Brownie macio de chocolate, preparado em pequenos lotes."
    },
    {
      id: "prod-demo-bytecare-1",
      name: "Diagnóstico de computador",
      businessName: "ByteCare Assistência Técnica",
      businessId: "business-demo-bytecare",
      categoryId: "eletronicos",
      categoryName: "Assistência Técnica",
      price: 49.9,
      rating: 4.9,
      image: "https://images.unsplash.com/photo-1587202372775-e229f172b9d7?w=600&auto=format&fit=crop&q=80",
      badge: "Agendamento",
      description: "Avaliação inicial do computador com horário marcado na assistência técnica."
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

const DEMO_PRODUCT_CATALOGS = {
  restaurant: {
    items: ["Hambúrguer artesanal", "Prato executivo", "Sanduíche especial", "Combo da casa", "Opção vegetariana", "Porção para compartilhar", "Salada fresca", "Suco natural", "Sobremesa artesanal", "Refeição família"],
    variants: ["Individual", "Para compartilhar"],
    basePrice: 12,
    priceRange: 45
  },
  market: {
    items: ["Cesta de frutas", "Café torrado", "Azeite extra virgem", "Arroz integral", "Massa artesanal", "Suco natural", "Queijo selecionado", "Pão de forma", "Chocolate", "Kit de mercearia"],
    variants: ["Unidade", "Kit econômico"],
    basePrice: 5,
    priceRange: 55
  },
  pharmacy: {
    items: ["Kit de higiene", "Protetor solar", "Hidratante corporal", "Shampoo diário", "Creme dental", "Sabonete líquido", "Vitamina C", "Álcool em gel", "Curativos adesivos", "Kit de cuidados pessoais"],
    variants: ["Unidade", "Kit econômico"],
    basePrice: 8,
    priceRange: 65
  },
  electronics: {
    items: ["Cabo USB-C", "Carregador rápido", "Mouse sem fio", "Teclado compacto", "Webcam HD", "Suporte para notebook", "Caixa de som portátil", "Power bank", "Hub USB", "Luminária de mesa"],
    variants: ["Versão básica", "Versão premium"],
    basePrice: 19,
    priceRange: 180
  },
  fashion: {
    items: ["Camiseta básica", "Calça jeans", "Jaqueta leve", "Vestido casual", "Moletom confortável", "Bermuda urbana", "Camisa social", "Bolsa transversal", "Tênis casual", "Acessório da estação"],
    variants: ["Tamanho P/M", "Tamanho G/GG"],
    basePrice: 25,
    priceRange: 160
  },
  beauty: {
    items: ["Sérum facial", "Hidratante facial", "Protetor solar", "Shampoo nutritivo", "Máscara capilar", "Batom hidratante", "Sabonete facial", "Óleo capilar", "Kit de cuidados", "Creme para mãos"],
    variants: ["Tamanho compacto", "Tamanho família"],
    basePrice: 12,
    priceRange: 90
  },
  home: {
    items: ["Vaso decorativo", "Luminária de mesa", "Organizador multiuso", "Manta para sofá", "Almofada decorativa", "Porta-retratos", "Kit de cozinha", "Planta ornamental", "Cesto organizador", "Acessório para casa"],
    variants: ["Tamanho pequeno", "Tamanho grande"],
    basePrice: 15,
    priceRange: 125
  },
  general: {
    items: ["Novidade da semana", "Seleção especial", "Kit para presente", "Produto favorito", "Edição limitada", "Escolha da equipe", "Oferta do dia", "Kit essencial", "Lançamento recente", "Mais vendido"],
    variants: ["Opção clássica", "Opção especial"],
    basePrice: 10,
    priceRange: 70
  },
  acai: {
    items: ["Açaí tradicional", "Açaí com banana", "Açaí com morango", "Açaí com granola", "Açaí com paçoca", "Açaí com leite em pó", "Açaí tropical", "Açaí com frutas", "Açaí zero", "Smoothie de açaí"],
    variants: ["300 ml", "500 ml"],
    basePrice: 12,
    priceRange: 28
  },
  bakery: {
    items: ["Pão francês", "Pão de queijo", "Croissant", "Pão integral", "Bolo caseiro", "Sonho recheado", "Pão de chocolate", "Torta salgada", "Biscoito amanteigado", "Cesta de café da manhã"],
    variants: ["Unidade", "Kit com 6"],
    basePrice: 4,
    priceRange: 35
  },
  pizza: {
    items: ["Pizza Margherita", "Pizza de calabresa", "Pizza quatro queijos", "Pizza de frango", "Pizza portuguesa", "Pizza vegetariana", "Pizza de pepperoni", "Pizza napolitana", "Pizza doce de chocolate", "Combo pizza e bebida"],
    variants: ["Tamanho médio", "Tamanho grande"],
    basePrice: 29,
    priceRange: 45
  },
  produce: {
    items: ["Banana prata", "Maçã Fuji", "Laranja pera", "Tomate italiano", "Folhas para salada", "Abacate maduro", "Cenoura fresca", "Batata selecionada", "Mix de legumes", "Cesta de frutas"],
    variants: ["500 g", "1 kg"],
    basePrice: 4,
    priceRange: 28
  },
  books: {
    items: ["Romance contemporâneo", "Livro infantil ilustrado", "Suspense policial", "Biografia inspiradora", "Fantasia e aventura", "Desenvolvimento pessoal", "História do Brasil", "Coletânea de poesia", "Livro de culinária", "Ciência para curiosos"],
    variants: ["Capa comum", "Capa dura"],
    basePrice: 24,
    priceRange: 65
  },
  flowers: {
    items: ["Buquê de rosas", "Buquê de flores do campo", "Girassóis da estação", "Orquídea decorativa", "Suculenta em vaso", "Arranjo para presente", "Planta para interior", "Buquê colorido", "Kit de jardinagem", "Vaso de flores frescas"],
    variants: ["Tamanho compacto", "Tamanho especial"],
    basePrice: 18,
    priceRange: 115
  },
  pet: {
    items: ["Ração para cães", "Ração para gatos", "Petisco natural", "Brinquedo interativo", "Coleira ajustável", "Areia higiênica", "Caminha confortável", "Comedouro para pets", "Shampoo para pets", "Kit passeio"],
    variants: ["Embalagem compacta", "Embalagem econômica"],
    basePrice: 9,
    priceRange: 110
  },
  sports: {
    items: ["Camiseta esportiva", "Legging para treino", "Short esportivo", "Jaqueta corta-vento", "Meias esportivas", "Garrafa térmica", "Mochila esportiva", "Boné para corrida", "Tênis para caminhada", "Top esportivo"],
    variants: ["Tamanho P/M", "Tamanho G/GG"],
    basePrice: 19,
    priceRange: 150
  },
  salon: {
    items: ["Escova e finalização", "Hidratação capilar", "Corte feminino", "Coloração capilar", "Manicure", "Pedicure", "Design de sobrancelhas", "Tratamento reconstrutor", "Penteado para evento", "Combo de cuidados"],
    variants: ["Sessão avulsa", "Combo especial"],
    basePrice: 25,
    priceRange: 105
  },
  barber: {
    items: ["Corte social", "Corte degradê", "Barba tradicional", "Barba desenhada", "Combo corte e barba", "Corte infantil", "Acabamento e pezinho", "Hidratação capilar", "Sobrancelha masculina", "Pigmentação de barba"],
    variants: ["Serviço avulso", "Combo especial"],
    basePrice: 20,
    priceRange: 75
  },
  dessert: {
    items: ["Brigadeiro artesanal", "Brownie de chocolate", "Bolo de cenoura", "Cheesecake da casa", "Cookie recheado", "Pudim caseiro", "Torta de frutas", "Cupcake decorado", "Caixa de doces", "Trufa de chocolate"],
    variants: ["Unidade", "Caixa com 6"],
    basePrice: 5,
    priceRange: 45
  },
  computerRepair: {
    items: ["Diagnóstico de computador", "Limpeza interna e pasta térmica", "Instalação de sistema operacional", "Remoção de vírus e otimização", "Upgrade de memória RAM", "Instalação de SSD", "Montagem de computador", "Configuração de rede Wi-Fi", "Recuperação de arquivos", "Suporte técnico para computador"],
    variants: ["Atendimento presencial", "Atendimento remoto"],
    basePrice: 25,
    priceRange: 135
  }
};

const DEMO_CATALOG_TYPE_BY_STORE = {
  "bistro-artesanal": "restaurant",
  "emporio-verde": "market",
  "drogaria-vital": "pharmacy",
  "techvolt": "electronics",
  "urbana-chic": "fashion",
  "glow-cosmeticos": "beauty",
  "zen-casa": "home",
  "patinhas-pet": "pet",
  "ifood-demo": "restaurant",
  "mercado-livre-demo": "electronics",
  "acai-da-praca": "acai",
  "barbearia-navalha": "barber",
  "business-demo-padaria": "bakery",
  "business-demo-pizzaria": "pizza",
  "business-demo-hortifruti": "produce",
  "business-demo-farmacia": "pharmacy",
  "business-demo-livraria": "books",
  "business-demo-floricultura": "flowers",
  "business-demo-pet": "pet",
  "business-demo-esportes": "sports",
  "business-demo-salao": "salon",
  "business-demo-doceria": "dessert",
  "business-demo-bytecare": "computerRepair"
};

const DEFAULT_CATALOG_TYPE_BY_CATEGORY = {
  alimentacao: "restaurant",
  supermercados: "market",
  farmacias: "pharmacy",
  roupas: "fashion",
  eletronicos: "electronics",
  beleza: "beauty",
  casa: "home",
  outros: "general"
};

// Não gerar produtos de preenchimento que reaproveitem a imagem de outro item.

// Exporta globalmente para uso direto no navegador sem necessidade de build tool
window.MARKETPLACE_DATA = MARKETPLACE_DATA;

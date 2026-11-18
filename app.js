// ==========================================================================
// CONECTA WEB SERVICE & MARKETPLACE - APLICAÇÃO INTERATIVA (app.js)
// Sistema completo de marketplace multi-loja em Português do Brasil (pt-BR)
// ==========================================================================

document.addEventListener('DOMContentLoaded', () => {
  // Inicialização de Estado da Aplicação
  const state = {
    cart: [],
    appointments: [],
    wishlist: new Set(),
    activeCategory: 'all',
    searchQuery: '',
    priceFilter: 'all',
    visibleProductCount: 16,
    appliedCoupon: null,
    currentUser: null,
    activeOrder: null,
    pendingAppointment: null,
    orderStepInterval: null
  };
  const MARKETPLACE_STORAGE_KEY = 'webservice-marketplace-catalog';
  const APPOINTMENTS_STORAGE_KEY = 'webservice-marketplace-appointments';
  const MARKETPLACE_CATALOG_VERSION = 3;
  const PRODUCT_PAGE_SIZE = 16;

  // Cupons válidos de demonstração
  const AVAILABLE_COUPONS = {
    'WEBSERVICE10': { type: 'fixed', value: 10.0, minTotal: 30.0, label: 'R$ 10 de desconto' },
    'CONECTA10': { type: 'fixed', value: 10.0, minTotal: 30.0, label: 'R$ 10 de desconto' },
    'FRETEFREE': { type: 'shipping', value: 0.0, minTotal: 40.0, label: 'Frete Grátis' },
    'PRIMEIRA15': { type: 'percent', value: 0.15, minTotal: 50.0, label: '15% de desconto' }
  };

  // Função auxiliar para formatar valores em reais.
  function formatMoney(amount) {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(amount);
  }

  function getLocalDateValue(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  function isAppointmentService(product) {
    return product.appointmentBased === true ||
      MARKETPLACE_DATA.businesses.some(business => business.id === product.businessId && business.appointmentBased === true);
  }

  function getAvailableAppointmentTimes(dateValue, businessId) {
    if (!dateValue) return [];
    const now = new Date();
    const isToday = dateValue === getLocalDateValue(now);
    const currentMinutes = now.getHours() * 60 + now.getMinutes();
    const times = [];

    for (let minutes = 9 * 60; minutes <= 17 * 60; minutes += 30) {
      if (isToday && minutes <= currentMinutes) continue;
      const time = `${String(Math.floor(minutes / 60)).padStart(2, '0')}:${String(minutes % 60).padStart(2, '0')}`;
      const alreadySelected = state.cart.some(item =>
        item.appointment &&
        item.product.businessId === businessId &&
        item.appointment.date === dateValue &&
        item.appointment.time === time
      );
      if (!alreadySelected) times.push(time);
    }

    return times;
  }

  function formatAppointmentDate(dateValue) {
    const [year, month, day] = dateValue.split('-').map(Number);
    return new Date(year, month - 1, day).toLocaleDateString('pt-BR');
  }

  function loadSavedAppointments() {
    try {
      const saved = localStorage.getItem(APPOINTMENTS_STORAGE_KEY);
      if (!saved) return;
      const appointments = JSON.parse(saved);
      const validAppointments = Array.isArray(appointments) && appointments.every(appointment =>
        appointment &&
        typeof appointment.id === 'string' &&
        appointment.product &&
        typeof appointment.product.name === 'string' &&
        typeof appointment.product.businessName === 'string' &&
        Number.isFinite(appointment.product.price) &&
        /^\d{4}-\d{2}-\d{2}$/.test(appointment.date) &&
        /^\d{2}:\d{2}$/.test(appointment.time) &&
        typeof appointment.orderId === 'string'
      );
      if (!validAppointments) {
        throw new Error('Formato de agendamentos salvo é inválido.');
      }
      state.appointments = appointments;
    } catch (error) {
      console.error('Não foi possível carregar os agendamentos salvos.', error);
      showToast('Não foi possível carregar os agendamentos salvos neste navegador.', 'error');
    }
  }

  function saveAppointments() {
    try {
      localStorage.setItem(APPOINTMENTS_STORAGE_KEY, JSON.stringify(state.appointments));
      return true;
    } catch (error) {
      console.error('Não foi possível salvar os agendamentos.', error);
      showToast('Não foi possível salvar os agendamentos neste navegador.', 'error');
      return false;
    }
  }

  // Notificações Toast
  function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast';
    
    let iconName = 'check-circle';
    let iconColor = '#39BDF5';
    if (type === 'error') {
      iconName = 'alert-circle';
      iconColor = '#EF4444';
    } else if (type === 'info') {
      iconName = 'info';
      iconColor = '#3B82F6';
    }

    const icon = document.createElement('i');
    icon.setAttribute('data-lucide', iconName);
    icon.style.color = iconColor;
    icon.style.width = '20px';
    icon.style.height = '20px';
    icon.style.flexShrink = '0';
    const text = document.createElement('span');
    text.style.flexGrow = '1';
    text.textContent = message;
    toast.append(icon, text);

    container.appendChild(toast);
    if (window.lucide) {
      lucide.createIcons({ root: toast });
    }

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(100%)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  }

  function escapeHTML(value) {
    return String(value).replace(/[&<>"']/g, character => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    })[character]);
  }

  const PRODUCT_IMAGE_FALLBACKS = {
    alimentacao: [
      'https://images.unsplash.com/photo-1568901346375-23c9450c58cd',
      'https://images.unsplash.com/photo-1513104890138-7c749659a591',
      'https://images.unsplash.com/photo-1550547660-d9450f859349',
      'https://images.unsplash.com/photo-1578985545062-69928b1d9587'
    ],
    supermercados: [
      'https://images.unsplash.com/photo-1542838132-92c53300491e',
      'https://images.unsplash.com/photo-1610832958506-aa56368176cf',
      'https://images.unsplash.com/photo-1540420773420-3366772f4999',
      'https://images.unsplash.com/photo-1509440159596-0249088772ff'
    ],
    farmacias: [
      'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae',
      'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b',
      'https://images.unsplash.com/photo-1556228720-195a672e8a03',
      'https://images.unsplash.com/photo-1584017911766-d451b3d0e843'
    ],
    eletronicos: [
      'https://images.unsplash.com/photo-1505740420928-5e560c06d30e',
      'https://images.unsplash.com/photo-1587829741301-dc798b83add3',
      'https://images.unsplash.com/photo-1496181133206-80ce9b88a853',
      'https://images.unsplash.com/photo-1484704849700-f032a568e944'
    ],
    roupas: [
      'https://images.unsplash.com/photo-1544441893-675973e31985',
      'https://images.unsplash.com/photo-1538805060514-97d9cc17730c',
      'https://images.unsplash.com/photo-1542291026-7eec264c27ff',
      'https://images.unsplash.com/photo-1445205170230-053b83016050'
    ],
    beleza: [
      'https://images.unsplash.com/photo-1560066984-138dadb4c035',
      'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9',
      'https://images.unsplash.com/photo-1620916566398-39f1143ab7be',
      'https://images.unsplash.com/photo-1527799820374-dcf8d9d4a388'
    ],
    casa: [
      'https://images.unsplash.com/photo-1507473885765-e6ed057f782c',
      'https://images.unsplash.com/photo-1490750967868-88aa4486c946',
      'https://images.unsplash.com/photo-1484101403633-562f891dc89a',
      'https://images.unsplash.com/photo-1485955900006-10f4d324d411'
    ],
    outros: [
      'https://images.unsplash.com/photo-1568640347023-a616a30bc3bd',
      'https://images.unsplash.com/photo-1544947950-fa07a98d237f',
      'https://images.unsplash.com/photo-1573865526739-10659fec78a5',
      'https://images.unsplash.com/photo-1589924691995-400dc9ecc119'
    ]
  };

  function getProductImage(product) {
    if (product && String(product.image || '').trim()) return product.image;
    const pool = PRODUCT_IMAGE_FALLBACKS[product && product.categoryId] || [
      'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=600&auto=format&fit=crop&q=80',
      'https://images.unsplash.com/photo-1472851294608-062f824d29cc?w=600&auto=format&fit=crop&q=80',
      'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&auto=format&fit=crop&q=80'
    ];
    const seed = String(product && (product.id || product.name) || '');
    let hash = 0;
    for (let i = 0; i < seed.length; i++) hash = ((hash * 31) + seed.charCodeAt(i)) >>> 0;
    const url = pool[hash % pool.length];
    return url.includes('?') ? url : `${url}?w=600&auto=format&fit=crop&q=80`;
  }

  // Imagens extras para produtos cadastrados, sempre escolhidas sem repetir arquivos.
  const UNIQUE_PRODUCT_IMAGES = [
    'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1544441893-675973e31985?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1568640347023-a616a30bc3bd?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1584017911766-d451b3d0e843?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1550547660-d9450f859349?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=600&auto=format&fit=crop&q=80',
    'acai-bowl.svg',
    'https://images.unsplash.com/photo-1621607512214-68297480165e?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1579751626657-72bc17010498?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1593560708920-61dd98c46a4e?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1587854692152-cbe660dbde88?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1497250681960-ef046c08a56e?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1558788353-f76d92427f16?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1573865526739-10659fec78a5?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1527799820374-dcf8d9d4a388?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1551024601-bec78aea704b?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1528712306091-ed0763094c98?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1482049016688-2d3e1b311543?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1547592180-85f173990554?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1476224203421-9ac39bcb3327?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1466637574441-749b8f19452f?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1579113800032-c38bd7635818?w=600&auto=format&fit=crop&q=80'
  ];

  const PRODUCT_IMAGE_BY_ID = {
    'prod-1': 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&auto=format&fit=crop&q=80',
    'prod-2': 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=600&auto=format&fit=crop&q=80',
    'prod-3': 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&auto=format&fit=crop&q=80',
    'prod-4': 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80',
    'prod-5': 'https://images.unsplash.com/photo-1544441893-675973e31985?w=600&auto=format&fit=crop&q=80',
    'prod-6': 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=600&auto=format&fit=crop&q=80',
    'prod-7': 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=600&auto=format&fit=crop&q=80',
    'prod-8': 'https://images.unsplash.com/photo-1568640347023-a616a30bc3bd?w=600&auto=format&fit=crop&q=80',
    'prod-9': 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600&auto=format&fit=crop&q=80',
    'prod-10': 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600&auto=format&fit=crop&q=80',
    'prod-11': 'https://images.unsplash.com/photo-1584017911766-d451b3d0e843?w=600&auto=format&fit=crop&q=80',
    'prod-12': 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&auto=format&fit=crop&q=80',
    'prod-ifood-1': 'https://images.unsplash.com/photo-1550547660-d9450f859349?w=600&auto=format&fit=crop&q=80',
    'prod-mercado-livre-1': 'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=600&auto=format&fit=crop&q=80',
    'prod-acai-1': 'acai-bowl.svg',
    'prod-barbearia-1': 'https://images.unsplash.com/photo-1621607512214-68297480165e?w=600&auto=format&fit=crop&q=80',
    'prod-demo-padaria-1': 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=600&auto=format&fit=crop&q=80',
    'prod-demo-padaria-2': 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&auto=format&fit=crop&q=80',
    'prod-demo-pizzaria-1': 'https://images.unsplash.com/photo-1579751626657-72bc17010498?w=600&auto=format&fit=crop&q=80',
    'prod-demo-pizzaria-2': 'https://images.unsplash.com/photo-1593560708920-61dd98c46a4e?w=600&auto=format&fit=crop&q=80',
    'prod-demo-hortifruti-1': 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=600&auto=format&fit=crop&q=80',
    'prod-demo-hortifruti-2': 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=600&auto=format&fit=crop&q=80',
    'prod-demo-farmacia-1': 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600&auto=format&fit=crop&q=80',
    'prod-demo-farmacia-2': 'https://images.unsplash.com/photo-1587854692152-cbe660dbde88?w=600&auto=format&fit=crop&q=80',
    'prod-demo-livraria-1': 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=600&auto=format&fit=crop&q=80',
    'prod-demo-livraria-2': 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=600&auto=format&fit=crop&q=80',
    'prod-demo-floricultura-1': 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?w=600&auto=format&fit=crop&q=80',
    'prod-demo-floricultura-2': 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=600&auto=format&fit=crop&q=80',
    'prod-demo-pet-1': 'https://images.unsplash.com/photo-1558788353-f76d92427f16?w=600&auto=format&fit=crop&q=80',
    'prod-demo-pet-2': 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?w=600&auto=format&fit=crop&q=80',
    'prod-demo-esportes-1': 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=600&auto=format&fit=crop&q=80',
    'prod-demo-esportes-2': 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=80',
    'prod-demo-salao-1': 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=600&auto=format&fit=crop&q=80',
    'prod-demo-salao-2': 'https://images.unsplash.com/photo-1527799820374-dcf8d9d4a388?w=600&auto=format&fit=crop&q=80',
    'prod-demo-doceria-1': 'https://images.unsplash.com/photo-1551024601-bec78aea704b?w=600&auto=format&fit=crop&q=80',
    'prod-demo-doceria-2': 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=600&auto=format&fit=crop&q=80',
    'prod-demo-bytecare-1': 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?w=600&auto=format&fit=crop&q=80'
  };

  function getProductImageKey(image) {
    const value = String(image || '').trim();
    if (!value) return '';
    try {
      const url = new URL(value, window.location.href);
      return `${url.origin}${url.pathname}`.toLowerCase();
    } catch (error) {
      return value.split(/[?#]/, 1)[0].toLowerCase();
    }
  }

  function ensureUniqueProductImages() {
    if (!Array.isArray(MARKETPLACE_DATA.products)) return false;
    const usedImages = new Set();
    let changed = false;

    MARKETPLACE_DATA.products.forEach(product => {
      const mappedImage = PRODUCT_IMAGE_BY_ID[product.id];
      if (!mappedImage) return;
      if (product.image !== mappedImage) changed = true;
      product.image = mappedImage;
      const imageKey = getProductImageKey(mappedImage);
      if (usedImages.has(imageKey)) {
        throw new Error(`A imagem do produto "${product.name}" está repetida no catálogo.`);
      }
      usedImages.add(imageKey);
    });

    MARKETPLACE_DATA.products.forEach(product => {
      if (PRODUCT_IMAGE_BY_ID[product.id]) return;

      const currentImage = String(product.image || '').trim();
      const currentKey = getProductImageKey(currentImage);
      if (currentImage && !usedImages.has(currentKey)) {
        usedImages.add(currentKey);
        return;
      }

      const categoryImages = PRODUCT_IMAGE_FALLBACKS[product.categoryId] || [];
      const availableImage = [...categoryImages, ...UNIQUE_PRODUCT_IMAGES].find(candidate => {
        const url = candidate.includes('?')
          ? candidate
          : `${candidate}?w=600&auto=format&fit=crop&q=80`;
        return !usedImages.has(getProductImageKey(url));
      });

      if (!availableImage) {
        throw new Error(`Não há imagens exclusivas disponíveis para "${product.name}".`);
      }

      product.image = availableImage.includes('?')
        ? availableImage
        : `${availableImage}?w=600&auto=format&fit=crop&q=80`;
      usedImages.add(getProductImageKey(product.image));
      changed = true;
    });

    return changed;
  }

  function loadSavedCatalog() {
    try {
      const saved = localStorage.getItem(MARKETPLACE_STORAGE_KEY);
      if (!saved) return;
      const catalog = JSON.parse(saved);
      if (!catalog || !Array.isArray(catalog.businesses) || !Array.isArray(catalog.products)) {
        throw new Error('Formato de catálogo inválido.');
      }
      const needsCatalogMigration = catalog.version !== MARKETPLACE_CATALOG_VERSION;
      const previousAcaiImage = 'https://images.unsplash.com/photo-1590301157890-4810ed352733';
      const demoBusinesses = MARKETPLACE_DATA.businesses.filter(business => business.id.startsWith('business-demo-'));
      const demoProducts = MARKETPLACE_DATA.products.filter(product => product.id.startsWith('prod-demo-') || product.id.startsWith('catalog-'));
      const appointmentBusinessIds = new Set(
        MARKETPLACE_DATA.businesses.filter(business => business.appointmentBased).map(business => business.id)
      );
      let migrated = false;
      const savedBusinesses = catalog.businesses.map(business => {
        const updatedBusiness = { ...business };
        if (updatedBusiness.id === 'acai-da-praca' &&
            (String(updatedBusiness.image || '').startsWith(previousAcaiImage) || String(updatedBusiness.logo || '').startsWith(previousAcaiImage))) {
          if (String(updatedBusiness.image || '').startsWith(previousAcaiImage)) updatedBusiness.image = 'acai-bowl.svg';
          if (String(updatedBusiness.logo || '').startsWith(previousAcaiImage)) updatedBusiness.logo = 'acai-bowl.svg';
          migrated = true;
        }
        if (appointmentBusinessIds.has(updatedBusiness.id) && updatedBusiness.appointmentBased !== true) {
          updatedBusiness.appointmentBased = true;
          migrated = true;
        }
        return updatedBusiness;
      });
      const savedProducts = catalog.products.filter(product => {
        if (!product.id.startsWith('catalog-')) return true;
        migrated = true;
        return false;
      }).map(product => {
        const mappedImage = PRODUCT_IMAGE_BY_ID[product.id];
        if (mappedImage) {
          if (product.image !== mappedImage) migrated = true;
          return { ...product, image: mappedImage };
        }
        if (product.id === 'prod-acai-1' && String(product.image || '').startsWith(previousAcaiImage)) {
          migrated = true;
          return { ...product, image: 'acai-bowl.svg' };
        }
        return product;
      });
      const savedBusinessIds = new Set(savedBusinesses.map(business => business.id));
      const savedProductIds = new Set(savedProducts.map(product => product.id));
      const missingDemoBusinesses = needsCatalogMigration ? demoBusinesses.filter(business => !savedBusinessIds.has(business.id)) : [];
      const missingDemoProducts = needsCatalogMigration ? demoProducts.filter(product => !savedProductIds.has(product.id)) : [];
      MARKETPLACE_DATA.businesses = [...savedBusinesses, ...missingDemoBusinesses];
      MARKETPLACE_DATA.products = [...savedProducts, ...missingDemoProducts];
      migrated = ensureUniqueProductImages() || migrated;
      migrated = migrated || needsCatalogMigration || missingDemoBusinesses.length > 0 || missingDemoProducts.length > 0;
      if (migrated) saveCatalog();
    } catch (error) {
      console.error('Não foi possível carregar o catálogo salvo.', error);
      showToast('Não foi possível carregar o catálogo salvo. Os dados de demonstração serão exibidos.', 'error');
    }
  }

  function saveCatalog() {
    try {
      ensureUniqueProductImages();
      localStorage.setItem(MARKETPLACE_STORAGE_KEY, JSON.stringify({
        version: MARKETPLACE_CATALOG_VERSION,
        businesses: MARKETPLACE_DATA.businesses,
        products: MARKETPLACE_DATA.products
      }));
      return true;
    } catch (error) {
      console.error('Não foi possível salvar as alterações do catálogo.', error);
      showToast('Não foi possível salvar no navegador. As alterações ficarão disponíveis somente nesta sessão.', 'error');
      return false;
    }
  }

  // ========================================================================
  // RENDERIZAÇÃO DE COMPONENTES NA TELA
  // ========================================================================

  // 1. Renderizar Categorias Interativas
  function renderCategories() {
    const grid = document.getElementById('categories-grid');
    if (!grid) return;

    grid.innerHTML = MARKETPLACE_DATA.categories.map(cat => `
      <div 
        class="category-card glow-card bg-[#0B1B2A] rounded-2xl p-5 cursor-pointer text-left transition-all duration-300 group ${state.activeCategory === cat.id ? 'border-[#39BDF5] shadow-[0_0_20px_rgba(57,189,245,0.25)]' : 'border-[#162C46]'}"
        data-category-id="${cat.id}"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br ${cat.gradient} flex items-center justify-center text-[#39BDF5] border border-[#39BDF5]/30 group-hover:scale-110 group-hover:border-[#39BDF5] transition-transform">
            <i data-lucide="${cat.icon}" class="w-6 h-6"></i>
          </div>
          <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-[#071522] text-[#39BDF5] border border-[#162C46]">
            ${cat.itemCount}+ itens
          </span>
        </div>
        <h3 class="text-lg font-bold text-white group-hover:text-[#39BDF5] transition-colors mb-1.5 flex items-center justify-between">
          ${cat.name}
          <i data-lucide="chevron-right" class="w-4 h-4 opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all text-[#39BDF5]"></i>
        </h3>
        <p class="text-sm text-[#B8C7D8] leading-relaxed line-clamp-2">
          ${cat.description}
        </p>
      </div>
    `).join('');

    // Eventos de clique nas categorias
    grid.querySelectorAll('.category-card').forEach(card => {
      card.addEventListener('click', () => {
        const catId = card.getAttribute('data-category-id');
        filterByCategory(catId);
        // Rola suavemente até a seção de produtos
        const prodSection = document.getElementById('produtos');
        if (prodSection) {
          prodSection.scrollIntoView({ behavior: 'smooth' });
        }
      });
    });
  }

  // 2. Renderizar Empresas Parceiras
  function renderBusinesses(filterCat = 'all') {
    const grid = document.getElementById('businesses-grid');
    if (!grid) return;

    let list = MARKETPLACE_DATA.businesses;
    if (filterCat !== 'all') {
      list = list.filter(b => b.categoryId === filterCat);
    }

    if (list.length === 0) {
      grid.innerHTML = `
        <div class="col-span-full py-12 text-center text-[#B8C7D8]">
          <i data-lucide="store" class="w-12 h-12 mx-auto text-[#6C829D] mb-3"></i>
          <p class="text-lg font-medium text-white">Nenhum estabelecimento encontrado nesta categoria.</p>
          <p class="text-sm mt-1">Experimente selecionar outra categoria acima.</p>
        </div>
      `;
      if (window.lucide) lucide.createIcons();
      return;
    }

    grid.innerHTML = list.map(b => `
      <div class="business-card glow-card bg-[#0B1B2A] rounded-2xl overflow-hidden border border-[#162C46] flex flex-col justify-between group">
        <div>
          <!-- Imagem de Capa do Estabelecimento -->
          <div class="relative h-44 overflow-hidden">
            <img 
              src="${escapeHTML(b.image)}" 
              alt="${escapeHTML(b.name)}" 
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
              loading="lazy"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-[#0B1B2A] via-transparent to-black/30"></div>
            
            <!-- Badge de Categoria e Avaliação -->
            <div class="absolute top-3 left-3">
              <span class="badge-tech bg-[#030A12]/90 backdrop-blur-md">
                ${escapeHTML(b.badge)}
              </span>
            </div>
            <div class="absolute top-3 right-3 bg-[#030A12]/90 backdrop-blur-md px-2.5 py-1 rounded-full flex items-center gap-1.5 border border-[#162C46]">
              <i data-lucide="star" class="w-3.5 h-3.5 text-amber-400 fill-amber-400"></i>
              <span class="text-xs font-bold text-white">${b.rating.toFixed(1)}</span>
              <span class="text-[10px] text-[#6C829D]">(${b.reviewsCount})</span>
            </div>
            
            <!-- Logo Flutuante da Empresa -->
            <div class="absolute -bottom-4 left-5 w-12 h-12 rounded-xl overflow-hidden border-2 border-[#39BDF5] shadow-lg bg-[#071522]">
              <img src="${escapeHTML(b.logo)}" alt="Logo ${escapeHTML(b.name)}" class="w-full h-full object-cover" />
            </div>
          </div>

          <!-- Informações da Empresa -->
          <div class="p-5 pt-7">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-xs font-semibold text-[#39BDF5] tracking-wide uppercase">${escapeHTML(b.category)}</span>
              <span class="text-[#6C829D] text-xs">•</span>
              <span class="text-xs text-[#B8C7D8] flex items-center gap-1">
                <i data-lucide="map-pin" class="w-3 h-3 text-[#39BDF5]"></i> ${escapeHTML(b.neighborhood)}
              </span>
            </div>

            <h3 class="text-lg font-bold text-white group-hover:text-[#39BDF5] transition-colors mb-2">
              ${escapeHTML(b.name)}
            </h3>

            <p class="text-xs text-[#B8C7D8] line-clamp-2 leading-relaxed mb-4">
              ${escapeHTML(b.description)}
            </p>

            <div class="flex items-center justify-between pt-3 border-t border-[#162C46] text-xs text-[#B8C7D8]">
              <div class="flex items-center gap-1.5">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-[#39BDF5]"></i>
                <span>${escapeHTML(b.deliveryTime)}</span>
              </div>
              <div class="flex items-center gap-1.5 font-medium ${b.deliveryFee === 0 ? 'text-emerald-400 font-semibold' : 'text-[#F5F9FF]'}">
                <i data-lucide="bike" class="w-3.5 h-3.5 text-[#39BDF5]"></i>
                <span>${b.deliveryFee === 0 ? 'Entrega Grátis' : formatMoney(b.deliveryFee)}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="p-5 pt-0">
          <button 
            class="btn-view-business w-full py-2.5 rounded-xl border border-[#39BDF5]/40 hover:border-[#39BDF5] text-white hover:text-[#39BDF5] hover:bg-[#39BDF5]/10 font-semibold text-sm transition-all flex items-center justify-center gap-2"
            data-business-name="${escapeHTML(b.name)}"
            data-business-cat="${escapeHTML(b.categoryId)}"
          >
            <span>Ver Produtos da Loja</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </button>
        </div>
      </div>
    `).join('');

    grid.querySelectorAll('.btn-view-business').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const cat = btn.getAttribute('data-business-cat');
        filterByCategory(cat);
        const prodSection = document.getElementById('produtos');
        if (prodSection) prodSection.scrollIntoView({ behavior: 'smooth' });
      });
    });

    if (window.lucide) lucide.createIcons({ root: grid });
  }

  // 3. Renderizar Produtos em Destaque
  function renderProducts() {
    const grid = document.getElementById('products-grid');
    if (!grid) return;

    let filtered = MARKETPLACE_DATA.products;

    // Filtro por Categoria
    if (state.activeCategory !== 'all') {
      filtered = filtered.filter(p => p.categoryId === state.activeCategory);
    }

    // Filtro por Busca de Texto
    if (state.searchQuery.trim() !== '') {
      const q = state.searchQuery.toLowerCase().trim();
      filtered = filtered.filter(p => 
        p.name.toLowerCase().includes(q) || 
        p.businessName.toLowerCase().includes(q) ||
        p.description.toLowerCase().includes(q)
      );
    }

    // Filtro por Faixa de Preço
    if (state.priceFilter === 'under-50') {
      filtered = filtered.filter(p => p.price <= 50);
    } else if (state.priceFilter === '50-150') {
      filtered = filtered.filter(p => p.price > 50 && p.price <= 150);
    } else if (state.priceFilter === 'over-150') {
      filtered = filtered.filter(p => p.price > 150);
    }

    const visibleProducts = filtered.slice(0, state.visibleProductCount);

    // Contagem de resultados encontrados
    const counterEl = document.getElementById('products-count');
    if (counterEl) {
      counterEl.textContent = filtered.length > visibleProducts.length
        ? `Mostrando ${visibleProducts.length} de ${filtered.length} produtos`
        : `${filtered.length} produto${filtered.length === 1 ? '' : 's'} encontrado${filtered.length === 1 ? '' : 's'}`;
    }

    if (filtered.length === 0) {
      grid.innerHTML = `
        <div class="col-span-full py-16 text-center text-[#B8C7D8] bg-[#071522]/60 rounded-2xl border border-[#162C46]">
          <i data-lucide="search-x" class="w-14 h-14 mx-auto text-[#6C829D] mb-3"></i>
          <p class="text-xl font-bold text-white mb-1">Nenhum produto encontrado</p>
          <p class="text-sm text-[#B8C7D8] max-w-md mx-auto mb-5">
            Não encontramos itens correspondentes à sua pesquisa. Experimente limpar os filtros ou buscar por outro termo.
          </p>
          <button id="btn-reset-filters" class="btn-primary px-6 py-2.5 text-sm font-semibold">
            Limpar Filtros e Ver Todos
          </button>
        </div>
      `;
      const resetBtn = grid.querySelector('#btn-reset-filters');
      if (resetBtn) {
        resetBtn.addEventListener('click', () => {
          state.activeCategory = 'all';
          state.searchQuery = '';
          state.priceFilter = 'all';
          state.visibleProductCount = PRODUCT_PAGE_SIZE;
          document.querySelectorAll('#nav-search-input, #hero-search-input, #products-search-input').forEach(input => {
            input.value = '';
          });
          const priceFilter = document.getElementById('price-filter-select');
          if (priceFilter) priceFilter.value = 'all';
          updateCategoryFilterButtons();
          renderProducts();
          scrollToProductsSection();
        });
      }
      if (window.lucide) lucide.createIcons({ root: grid });
      return;
    }

    grid.innerHTML = visibleProducts.map(p => {
      const isWishlisted = state.wishlist.has(p.id);
      const discountPercent = p.originalPrice ? Math.round(((p.originalPrice - p.price) / p.originalPrice) * 100) : 0;

      return `
        <div class="product-card glow-card bg-[#0B1B2A] rounded-2xl overflow-hidden border border-[#162C46] flex flex-col justify-between group relative" data-product-id="${p.id}">
          <div>
            <!-- Imagem do Produto com Efeito Zoom -->
            <div class="relative h-52 overflow-hidden bg-[#071522] cursor-pointer btn-quick-view" data-product-id="${p.id}">
              <img 
                    src="${escapeHTML(getProductImage(p))}" 
                    alt="${escapeHTML(p.name)}" 
                class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-500" 
                loading="lazy"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-[#0B1B2A] via-transparent to-black/20 opacity-80"></div>

              <!-- Badges de Promoção / Destaque -->
              <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                <span class="badge-tech bg-[#030A12]/90 backdrop-blur-md">
                  ${escapeHTML(p.badge)}
                </span>
                ${discountPercent > 0 ? `
                  <span class="bg-rose-500/90 text-white text-[11px] font-bold px-2 py-0.5 rounded-full w-max shadow-md">
                    -${discountPercent}%
                  </span>
                ` : ''}
              </div>

              <!-- Botão de Favorito / Wishlist -->
              <button 
                class="btn-wishlist absolute top-3 right-3 w-9 h-9 rounded-full bg-[#030A12]/80 backdrop-blur-md border border-[#162C46] flex items-center justify-center transition-colors hover:border-[#39BDF5] hover:text-[#39BDF5] ${isWishlisted ? 'text-rose-500 border-rose-500/50' : 'text-[#B8C7D8]'}"
                data-product-id="${escapeHTML(p.id)}"
                title="Favoritar produto"
              >
                <i data-lucide="heart" class="w-4 h-4 ${isWishlisted ? 'fill-rose-500 text-rose-500' : ''}"></i>
              </button>

              <!-- Avaliação Flutuante -->
              <div class="absolute bottom-2.5 left-3 bg-[#030A12]/90 backdrop-blur-md px-2 py-0.5 rounded-md flex items-center gap-1 border border-[#162C46]">
                <i data-lucide="star" class="w-3 h-3 text-amber-400 fill-amber-400"></i>
                <span class="text-xs font-semibold text-white">${p.rating.toFixed(1)}</span>
              </div>
            </div>

            <!-- Dados do Produto -->
            <div class="p-5">
              <div class="flex items-center gap-1.5 text-xs text-[#39BDF5] font-medium mb-1">
                <i data-lucide="store" class="w-3.5 h-3.5"></i>
                <span class="truncate">${escapeHTML(p.businessName)}</span>
              </div>

              <h4 class="text-base font-bold text-white group-hover:text-[#39BDF5] transition-colors line-clamp-1 mb-1.5 cursor-pointer btn-quick-view" data-product-id="${escapeHTML(p.id)}">
                ${escapeHTML(p.name)}
              </h4>

              <p class="text-xs text-[#B8C7D8] line-clamp-2 leading-relaxed mb-4">
                ${escapeHTML(p.description)}
              </p>

              <!-- Preço em Reais Brasileiros (R$) -->
              <div class="flex items-baseline gap-2 mb-2">
                <span class="text-xl font-extrabold text-[#F5F9FF] font-['Outfit']">
                  ${formatMoney(p.price)}
                </span>
                ${p.originalPrice ? `
                  <span class="text-xs text-[#6C829D] line-through">
                    ${formatMoney(p.originalPrice)}
                  </span>
                ` : ''}
              </div>
            </div>
          </div>

          <!-- Botão Adicionar ao Carrinho -->
          <div class="p-5 pt-0">
            <button 
              class="btn-add-cart w-full py-2.5 rounded-xl bg-gradient-to-r from-[#087BD4] to-[#39BDF5] text-[#030A12] font-bold text-sm transition-all duration-300 hover:shadow-[0_0_20px_rgba(57,189,245,0.45)] hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2"
              data-product-id="${escapeHTML(p.id)}"
            >
              <i data-lucide="shopping-bag" class="w-4 h-4"></i>
              <span>${isAppointmentService(p) ? 'Agendar Horário' : 'Adicionar ao Carrinho'}</span>
            </button>
          </div>
        </div>
      `;
    }).join('');

    if (visibleProducts.length < filtered.length) {
      const loadMoreButton = document.createElement('button');
      loadMoreButton.type = 'button';
      loadMoreButton.className = 'btn-outline col-span-full justify-self-center px-6 py-3 text-sm font-semibold';
      loadMoreButton.textContent = `Carregar mais produtos (${filtered.length - visibleProducts.length} restantes)`;
      loadMoreButton.addEventListener('click', () => {
        state.visibleProductCount += PRODUCT_PAGE_SIZE;
        renderProducts();
      });
      grid.appendChild(loadMoreButton);
    }

    // Listeners de Adicionar ao Carrinho
    grid.querySelectorAll('.btn-add-cart').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const pId = btn.getAttribute('data-product-id');
        addToCart(pId);
      });
    });

    // Listeners de Favoritar
    grid.querySelectorAll('.btn-wishlist').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const pId = btn.getAttribute('data-product-id');
        toggleWishlist(pId);
      });
    });

    // Listeners de Quick View Modal
    grid.querySelectorAll('.btn-quick-view').forEach(elem => {
      elem.addEventListener('click', () => {
        const pId = elem.getAttribute('data-product-id');
        openQuickViewModal(pId);
      });
    });

    if (window.lucide) lucide.createIcons({ root: grid });
  }

  function renderManagerCatalog() {
    const businessSelect = document.querySelector('#manager-product-form [name="businessId"]');
    const catalogList = document.getElementById('manager-catalog-list');
    if (!businessSelect || !catalogList) return;

    const selectedBusinessId = businessSelect.value;
    businessSelect.innerHTML = MARKETPLACE_DATA.businesses.map(business => `
      <option value="${escapeHTML(business.id)}">${escapeHTML(business.name)}</option>
    `).join('');
    if (MARKETPLACE_DATA.businesses.some(business => business.id === selectedBusinessId)) {
      businessSelect.value = selectedBusinessId;
    }

    const productSubmit = document.querySelector('#manager-product-form button[type="submit"]');
    if (productSubmit) productSubmit.disabled = MARKETPLACE_DATA.businesses.length === 0;

    if (MARKETPLACE_DATA.businesses.length === 0) {
      catalogList.innerHTML = '<p class="text-xs text-[#B8C7D8]">Cadastre uma loja para começar seu catálogo.</p>';
      return;
    }

    catalogList.innerHTML = MARKETPLACE_DATA.businesses.map(business => {
      const businessProducts = MARKETPLACE_DATA.products.filter(product => product.businessId === business.id);
      return `
        <div class="rounded-xl border border-[#162C46] bg-[#0B1B2A] p-3">
          <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
              <p class="truncate text-xs font-bold text-white">${escapeHTML(business.name)}</p>
              <p class="text-[11px] text-[#B8C7D8]">${businessProducts.length} produto(s)</p>
            </div>
            <button type="button" class="manager-delete-business text-[11px] text-rose-300 hover:text-rose-200" data-business-id="${escapeHTML(business.id)}">Remover loja</button>
          </div>
          ${businessProducts.length ? `
            <ul class="mt-2 space-y-1 border-t border-[#162C46] pt-2">
              ${businessProducts.map(product => `
                <li class="flex items-center justify-between gap-3 text-[11px]">
                  <span class="min-w-0 truncate text-[#B8C7D8]">${escapeHTML(product.name)} · ${formatMoney(product.price)}</span>
                  <button type="button" class="manager-delete-product shrink-0 text-rose-300 hover:text-rose-200" data-product-id="${escapeHTML(product.id)}">Remover</button>
                </li>
              `).join('')}
            </ul>
          ` : '<p class="mt-2 border-t border-[#162C46] pt-2 text-[11px] text-[#6C829D]">Nenhum produto cadastrado.</p>'}
        </div>
      `;
    }).join('');

    catalogList.querySelectorAll('.manager-delete-business').forEach(button => {
      button.addEventListener('click', () => {
        const businessId = button.getAttribute('data-business-id');
        const business = MARKETPLACE_DATA.businesses.find(item => item.id === businessId);
        if (!business) return;
        MARKETPLACE_DATA.businesses = MARKETPLACE_DATA.businesses.filter(item => item.id !== businessId);
        MARKETPLACE_DATA.products = MARKETPLACE_DATA.products.filter(item => item.businessId !== businessId);
        state.cart = state.cart.filter(item => item.product.businessId !== businessId);
        const saved = saveCatalog();
        refreshCatalogViews();
        renderManagerCatalog();
        if (saved) showToast(`Loja "${business.name}" removida do catálogo.`, 'info');
      });
    });

    catalogList.querySelectorAll('.manager-delete-product').forEach(button => {
      button.addEventListener('click', () => {
        const productId = button.getAttribute('data-product-id');
        const product = MARKETPLACE_DATA.products.find(item => item.id === productId);
        if (!product) return;
        MARKETPLACE_DATA.products = MARKETPLACE_DATA.products.filter(item => item.id !== productId);
        state.cart = state.cart.filter(item => item.product.id !== productId);
        const saved = saveCatalog();
        refreshCatalogViews();
        renderManagerCatalog();
        if (saved) showToast(`"${product.name}" removido do catálogo.`, 'info');
      });
    });
  }

  function refreshCatalogViews() {
    state.activeCategory = 'all';
    state.searchQuery = '';
    state.priceFilter = 'all';
    state.visibleProductCount = PRODUCT_PAGE_SIZE;
    document.querySelectorAll('#nav-search-input, #hero-search-input, #products-search-input').forEach(input => {
      input.value = '';
    });
    const priceFilter = document.getElementById('price-filter-select');
    if (priceFilter) priceFilter.value = 'all';
    updateCategoryFilterButtons();
    renderBusinesses();
    renderProducts();
    updateCartUI();
  }

  function createBusiness(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const formData = new FormData(form);
    const name = String(formData.get('name') || '').trim();
    const categoryId = String(formData.get('categoryId') || '');
    const category = MARKETPLACE_DATA.categories.find(item => item.id === categoryId);
    if (!name || !category) {
      showToast('Informe um nome e uma categoria válida para a loja.', 'error');
      return;
    }

    const categoryImage = MARKETPLACE_DATA.businesses.find(item => item.categoryId === categoryId);
    MARKETPLACE_DATA.businesses.push({
      id: `custom-business-${Date.now()}`,
      name,
      category: category.shortName,
      categoryId,
      rating: 5,
      reviewsCount: 0,
      deliveryTime: categoryId === 'beleza' ? 'Agendamento' : '30 - 45 min',
      deliveryFee: 0,
      image: categoryImage ? categoryImage.image : 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=600&auto=format&fit=crop&q=80',
      logo: categoryImage ? categoryImage.logo : 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=150&auto=format&fit=crop&q=80',
      badge: 'Novo parceiro',
      neighborhood: String(formData.get('neighborhood') || '').trim() || 'São Paulo',
      description: 'Estabelecimento parceiro cadastrado no painel da loja.'
    });
    const saved = saveCatalog();
    form.reset();
    refreshCatalogViews();
    renderManagerCatalog();
    if (saved) showToast(`Loja "${name}" cadastrada com sucesso!`);
  }

  function createProduct(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const formData = new FormData(form);
    const businessId = String(formData.get('businessId') || '');
    const business = MARKETPLACE_DATA.businesses.find(item => item.id === businessId);
    const name = String(formData.get('name') || '').trim();
    const description = String(formData.get('description') || '').trim();
    const price = Number(formData.get('price'));
    const imageInput = String(formData.get('image') || '').trim();
    let image = '';

    if (!business || !name || !Number.isFinite(price) || price <= 0) {
      showToast('Selecione uma loja e informe o nome e um preço válido.', 'error');
      return;
    }
    if (imageInput) {
      try {
        const imageUrl = new URL(imageInput);
        if (imageUrl.protocol !== 'https:' && imageUrl.protocol !== 'http:') {
          throw new Error('URL de imagem deve usar HTTP ou HTTPS.');
        }
        image = imageUrl.href;
      } catch (error) {
        showToast('Informe uma URL de imagem válida iniciada por http:// ou https://.', 'error');
        return;
      }
    }

    const product = {
      id: `custom-product-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
      name,
      businessName: business.name,
      businessId: business.id,
      categoryId: business.categoryId,
      categoryName: business.category,
      price,
      rating: 5,
      image: image || getProductImage({ id: name, categoryId: business.categoryId }),
      badge: 'Novo item',
      description: description || 'Produto cadastrado pela loja.'
    };
    MARKETPLACE_DATA.products.push(product);
    const saved = saveCatalog();
    form.reset();
    refreshCatalogViews();
    renderManagerCatalog();
    if (saved) showToast(`"${name}" adicionado ao catálogo!`);
  }

  // 4. Renderizar Planos para Empresas Parceiras (Canvas: Fontes de Receita)
  function renderPartnerPlans() {
    const container = document.getElementById('plans-grid');
    if (!container) return;

    container.innerHTML = MARKETPLACE_DATA.partnerPlans.map(plan => `
      <div class="relative glow-card bg-[#0B1B2A] rounded-2xl p-7 border ${plan.isPopular ? 'border-[#39BDF5] shadow-[0_0_35px_rgba(57,189,245,0.22)] -translate-y-2' : 'border-[#162C46]'} flex flex-col justify-between">
        ${plan.isPopular ? `
          <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-[#087BD4] to-[#39BDF5] text-[#030A12] text-xs font-black uppercase tracking-wider px-4 py-1 rounded-full shadow-lg">
            ${plan.badge}
          </div>
        ` : ''}

        <div>
          <div class="mb-4">
            <span class="text-xs font-bold text-[#39BDF5] uppercase tracking-wider">${plan.commission}</span>
            <h3 class="text-2xl font-black text-white mt-1">${plan.name}</h3>
            <p class="text-sm text-[#B8C7D8] mt-2 leading-relaxed">${plan.tagline}</p>
          </div>

          <div class="py-5 my-4 border-y border-[#162C46]">
            <div class="flex items-baseline gap-1">
              <span class="text-4xl font-extrabold text-white font-['Outfit']">
                ${plan.price === 0 ? 'R$ 0' : formatMoney(plan.price)}
              </span>
              <span class="text-sm text-[#B8C7D8]">${plan.priceLabel}</span>
            </div>
          </div>

          <ul class="space-y-3 mb-8">
            ${plan.features.map(f => `
              <li class="flex items-start gap-2.5 text-sm text-[#F5F9FF]">
                <div class="w-5 h-5 rounded-full bg-[#39BDF5]/15 text-[#39BDF5] flex items-center justify-center shrink-0 mt-0.5 border border-[#39BDF5]/30">
                  <i data-lucide="check" class="w-3.5 h-3.5"></i>
                </div>
                <span>${f}</span>
              </li>
            `).join('')}
          </ul>
        </div>

        <button 
          class="btn-plan-select w-full py-3 rounded-full font-bold text-sm transition-all ${plan.isPopular ? 'btn-primary' : 'btn-outline'}"
          data-plan-id="${plan.id}"
          data-plan-name="${plan.name}"
        >
          ${plan.cta}
        </button>
      </div>
    `).join('');

    container.querySelectorAll('.btn-plan-select').forEach(btn => {
      btn.addEventListener('click', () => {
        const planName = btn.getAttribute('data-plan-name');
        openPartnerModal(planName);
      });
    });

    if (window.lucide) lucide.createIcons({ root: container });
  }

  // 5. Renderizar FAQ (Perguntas Frequentes)
  function renderFaq() {
    const container = document.getElementById('faq-accordion');
    if (!container) return;

    container.innerHTML = MARKETPLACE_DATA.faq.map((item, idx) => `
      <div class="faq-item border border-[#162C46] rounded-xl overflow-hidden bg-[#0B1B2A] transition-colors">
        <button 
          class="faq-header w-full p-5 text-left flex items-center justify-between gap-4 font-semibold text-white hover:text-[#39BDF5] transition-colors"
          data-faq-idx="${idx}"
        >
          <span class="text-base">${item.question}</span>
          <i data-lucide="chevron-down" class="w-5 h-5 text-[#39BDF5] transition-transform duration-300"></i>
        </button>
        <div class="faq-content hidden px-5 pb-5 text-sm text-[#B8C7D8] leading-relaxed border-t border-[#162C46]/50 pt-3">
          ${item.answer}
        </div>
      </div>
    `).join('');

    container.querySelectorAll('.faq-header').forEach(btn => {
      btn.addEventListener('click', () => {
        const content = btn.nextElementSibling;
        const icon = btn.querySelector('[data-lucide="chevron-down"]');
        const isOpen = !content.classList.contains('hidden');

        // Fecha todos os outros
        container.querySelectorAll('.faq-content').forEach(c => c.classList.add('hidden'));
        container.querySelectorAll('.faq-header i').forEach(i => i.style.transform = 'rotate(0deg)');

        if (!isOpen) {
          content.classList.remove('hidden');
          if (icon) icon.style.transform = 'rotate(180deg)';
        }
      });
    });

    if (window.lucide) lucide.createIcons({ root: container });
  }

  // ========================================================================
  // GERENCIAMENTO DO CARRINHO DE COMPRAS
  // ========================================================================

  function addToCart(productId, qty = 1, appointment = null) {
    const product = MARKETPLACE_DATA.products.find(p => p.id === productId);
    if (!product) return;

    if (isAppointmentService(product) && !appointment) {
      showAppointmentModal(product, qty);
      return;
    }

    const bookingId = appointment ? `appointment-${Date.now()}-${Math.random().toString(36).slice(2, 8)}` : product.id;
    const existingIndex = appointment ? -1 : state.cart.findIndex(item => item.product.id === productId && !item.appointment);
    if (existingIndex > -1) {
      state.cart[existingIndex].quantity += qty;
    } else {
      state.cart.push({
        id: bookingId,
        product,
        quantity: appointment ? 1 : qty,
        appointment
      });
    }

    updateCartUI();
    showToast(appointment
      ? `Horário de ${formatAppointmentDate(appointment.date)} às ${appointment.time} reservado no carrinho.`
      : `"${product.name}" adicionado ao carrinho!`);
  }

  function populateAppointmentTimes() {
    const timeSelect = document.getElementById('appointment-time');
    const dateInput = document.getElementById('appointment-date');
    const pending = state.pendingAppointment;
    if (!timeSelect || !dateInput || !pending) return;

    const times = getAvailableAppointmentTimes(dateInput.value, pending.businessId);
    timeSelect.innerHTML = times.length
      ? `<option value="">Selecione um horário</option>${times.map(time => `<option value="${time}">${time}</option>`).join('')}`
      : '<option value="">Sem horários disponíveis nesta data</option>';
  }

  function showAppointmentModal(product, quantity = 1) {
    const dateInput = document.getElementById('appointment-date');
    const modal = document.getElementById('appointment-modal');
    if (!dateInput || !modal) {
      showToast('Não foi possível abrir o agendamento deste serviço.', 'error');
      return;
    }

    const minimumDate = new Date();
    const tomorrow = new Date(minimumDate);
    tomorrow.setDate(tomorrow.getDate() + 1);
    dateInput.min = getLocalDateValue(minimumDate);
    dateInput.value = getLocalDateValue(tomorrow);
    state.pendingAppointment = {
      productId: product.id,
      businessId: product.businessId,
      quantity
    };

    document.getElementById('appointment-business').textContent = product.businessName;
    document.getElementById('appointment-service').textContent = product.name;
    document.getElementById('appointment-price').textContent = formatMoney(product.price);
    populateAppointmentTimes();
    openModal('appointment-modal');
  }

  function removeFromCart(cartItemId) {
    const item = state.cart.find(i => (i.id || i.product.id) === cartItemId);
    state.cart = state.cart.filter(i => (i.id || i.product.id) !== cartItemId);
    updateCartUI();
    if (item) {
      showToast(`Item removido do carrinho.`, 'info');
    }
  }

  function updateQuantity(cartItemId, delta) {
    const item = state.cart.find(i => (i.id || i.product.id) === cartItemId);
    if (!item) return;

    item.quantity += delta;
    if (item.quantity <= 0) {
      removeFromCart(cartItemId);
    } else {
      updateCartUI();
    }
  }

  function toggleWishlist(productId) {
    if (state.wishlist.has(productId)) {
      state.wishlist.delete(productId);
      showToast('Item removido dos favoritos.', 'info');
    } else {
      state.wishlist.add(productId);
      showToast('Item salvo nos seus favoritos!');
    }
    renderProducts();
  }

  function calculateCartTotals() {
    const subtotal = state.cart.reduce((sum, item) => sum + (item.product.price * item.quantity), 0);
    const hasDeliveryItems = state.cart.some(item => !item.appointment);
    
    // Frete padrão R$ 7,90 ou Grátis acima de R$ 100
    let deliveryFee = !hasDeliveryItems || subtotal > 100 || subtotal === 0 ? 0.00 : 7.90;
    
    // Desconto por cupom
    let discount = 0.00;
    if (state.appliedCoupon && subtotal >= state.appliedCoupon.minTotal) {
      if (state.appliedCoupon.type === 'fixed') {
        discount = state.appliedCoupon.value;
      } else if (state.appliedCoupon.type === 'percent') {
        discount = subtotal * state.appliedCoupon.value;
      } else if (state.appliedCoupon.type === 'shipping') {
        deliveryFee = 0.00;
        discount = 0.00;
      }
    }

    const total = Math.max(0, subtotal - discount + deliveryFee);

    return { subtotal, deliveryFee, discount, total };
  }

  function updateCartUI() {
    // 1. Contador no ícone do Navbar
    const totalCount = state.cart.reduce((sum, i) => sum + i.quantity, 0);
    const badges = document.querySelectorAll('.cart-badge');
    badges.forEach(b => {
      b.textContent = totalCount;
      if (totalCount > 0) {
        b.classList.remove('hidden');
        b.classList.add('flex');
      } else {
        b.classList.add('hidden');
        b.classList.remove('flex');
      }
    });
    updateAppointmentsUI();

    // 2. Itens no Drawer do Carrinho
    const cartItemsContainer = document.getElementById('cart-items-list');
    const emptyState = document.getElementById('cart-empty-state');
    const footerContainer = document.getElementById('cart-footer');

    if (!cartItemsContainer) return;

    if (state.cart.length === 0) {
      cartItemsContainer.innerHTML = '';
      if (emptyState) emptyState.classList.remove('hidden');
      if (footerContainer) footerContainer.classList.add('hidden');
      return;
    }

    if (emptyState) emptyState.classList.add('hidden');
    if (footerContainer) footerContainer.classList.remove('hidden');

    cartItemsContainer.innerHTML = state.cart.map(item => {
      const cartItemId = item.id || item.product.id;
      const appointmentDetails = item.appointment
        ? `<p class="text-[11px] text-[#A8E1FF] mt-1">Agendado: ${formatAppointmentDate(item.appointment.date)} às ${escapeHTML(item.appointment.time)}</p>`
        : '';
      return `
        <div class="cart-item-row flex items-center gap-3 p-3 rounded-xl bg-[#071522] border border-[#162C46]">
          <img
            src="${escapeHTML(getProductImage(item.product))}"
            alt="${item.product.name}"
            class="w-16 h-16 rounded-lg object-cover shrink-0 border border-[#162C46]"
          />
          <div class="flex-grow min-w-0">
            <p class="text-xs text-[#39BDF5] truncate">${item.product.businessName}</p>
            <h5 class="text-sm font-bold text-white truncate">${item.product.name}</h5>
            <p class="text-xs text-[#B8C7D8] font-semibold mt-0.5">${formatMoney(item.product.price)} un.</p>
            ${appointmentDetails}
          </div>

          <div class="flex flex-col items-end gap-1.5 shrink-0">
            <button
              class="btn-remove-item text-xs text-[#6C829D] hover:text-rose-400 p-1"
              data-cart-item-id="${escapeHTML(cartItemId)}"
              title="Remover item"
            >
              <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
            </button>
            ${item.appointment ? `
              <span class="text-[11px] text-[#B8C7D8]">1 horário</span>
            ` : `
              <div class="flex items-center gap-2 bg-[#030A12] px-2 py-1 rounded-lg border border-[#162C46]">
                <button class="btn-qty-minus text-xs text-[#B8C7D8] hover:text-[#39BDF5]" data-cart-item-id="${escapeHTML(cartItemId)}">-</button>
                <span class="text-xs font-bold text-white px-1">${item.quantity}</span>
                <button class="btn-qty-plus text-xs text-[#B8C7D8] hover:text-[#39BDF5]" data-cart-item-id="${escapeHTML(cartItemId)}">+</button>
              </div>
            `}
          </div>
        </div>
      `;
    }).join('');

    // Totais
    const { subtotal, deliveryFee, discount, total } = calculateCartTotals();

    const subtotalEl = document.getElementById('cart-subtotal');
    const shippingEl = document.getElementById('cart-shipping');
    const discountRow = document.getElementById('cart-discount-row');
    const discountEl = document.getElementById('cart-discount');
    const totalEl = document.getElementById('cart-total');

    if (subtotalEl) subtotalEl.textContent = formatMoney(subtotal);
    const hasDeliveryItems = state.cart.some(item => !item.appointment);
    if (shippingEl) shippingEl.textContent = hasDeliveryItems ? (deliveryFee === 0 ? 'Grátis' : formatMoney(deliveryFee)) : 'Não se aplica';
    
    if (discountRow && discountEl) {
      if (discount > 0) {
        discountRow.classList.remove('hidden');
        discountEl.textContent = `-${formatMoney(discount)}`;
      } else {
        discountRow.classList.add('hidden');
      }
    }

    if (totalEl) totalEl.textContent = formatMoney(total);

    // Listeners do Carrinho
    cartItemsContainer.querySelectorAll('.btn-qty-plus').forEach(b => {
      b.addEventListener('click', () => updateQuantity(b.getAttribute('data-cart-item-id'), 1));
    });
    cartItemsContainer.querySelectorAll('.btn-qty-minus').forEach(b => {
      b.addEventListener('click', () => updateQuantity(b.getAttribute('data-cart-item-id'), -1));
    });
    cartItemsContainer.querySelectorAll('.btn-remove-item').forEach(b => {
      b.addEventListener('click', () => removeFromCart(b.getAttribute('data-cart-item-id')));
    });

    if (window.lucide) lucide.createIcons({ root: cartItemsContainer });
  }

  function updateAppointmentsUI() {
    const pendingAppointments = state.cart
      .filter(item => item.appointment)
      .map(item => ({
        id: item.id || item.product.id,
        product: item.product,
        date: item.appointment.date,
        time: item.appointment.time,
        status: 'No carrinho',
        orderId: ''
      }));
    const confirmedAppointments = state.appointments.map(item => ({
      ...item,
      status: 'Confirmado'
    }));
    const appointments = [...confirmedAppointments, ...pendingAppointments]
      .sort((first, second) => `${first.date} ${first.time}`.localeCompare(`${second.date} ${second.time}`));
    const badges = document.querySelectorAll('.appointment-badge');
    badges.forEach(badge => {
      badge.textContent = appointments.length;
      badge.classList.toggle('hidden', appointments.length === 0);
      badge.classList.toggle('flex', appointments.length > 0);
    });

    const list = document.getElementById('appointments-list');
    const pageList = document.getElementById('appointments-page-list');
    const emptyState = document.getElementById('appointments-empty-state');
    if (!list) return;

    if (appointments.length === 0) {
      list.innerHTML = '';
      if (pageList) {
        pageList.innerHTML = `
          <div class="col-span-full rounded-2xl border border-[#162C46] bg-[#0B1B2A] p-8 text-center text-sm text-[#B8C7D8]">
            Você ainda não tem serviços agendados.
          </div>
        `;
      }
      if (emptyState) emptyState.classList.remove('hidden');
      return;
    }

    if (emptyState) emptyState.classList.add('hidden');
    const appointmentCards = appointments.map(appointment => {
      const confirmed = appointment.status === 'Confirmado';
      return `
        <article class="rounded-xl border border-[#162C46] bg-[#071522] p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="truncate text-xs font-semibold text-[#39BDF5]">${escapeHTML(appointment.product.businessName)}</p>
              <h4 class="mt-1 text-sm font-bold text-white">${escapeHTML(appointment.product.name)}</h4>
            </div>
            <span class="shrink-0 rounded-full ${confirmed ? 'bg-emerald-500/15 text-emerald-300' : 'bg-amber-500/15 text-amber-300'} px-2 py-1 text-[10px] font-bold">
              ${appointment.status}
            </span>
          </div>
          <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-[#B8C7D8]">
            <span class="inline-flex items-center gap-1.5">
              <i data-lucide="calendar-days" class="h-3.5 w-3.5 text-[#39BDF5]"></i>
              ${formatAppointmentDate(appointment.date)}
            </span>
            <span class="inline-flex items-center gap-1.5">
              <i data-lucide="clock-3" class="h-3.5 w-3.5 text-[#39BDF5]"></i>
              ${escapeHTML(appointment.time)}
            </span>
          </div>
          <div class="mt-3 flex items-center justify-between gap-3 border-t border-[#162C46] pt-3">
            <span class="text-sm font-bold text-white">${formatMoney(appointment.product.price)}</span>
            ${confirmed
              ? `<span class="text-[11px] text-[#6C829D]">Pedido #${escapeHTML(appointment.orderId)}</span>`
              : `<button type="button" class="btn-remove-pending-appointment text-xs font-semibold text-rose-300 hover:text-rose-200" data-cart-item-id="${escapeHTML(appointment.id)}">Remover</button>`
            }
          </div>
        </article>
      `;
    }).join('');
    list.innerHTML = appointmentCards;
    if (pageList) pageList.innerHTML = appointmentCards;

    document.querySelectorAll('.btn-remove-pending-appointment').forEach(button => {
      button.addEventListener('click', () => removeFromCart(button.getAttribute('data-cart-item-id')));
    });
    if (window.lucide) lucide.createIcons({ root: list });
  }

  // ========================================================================
  // FILTRAGEM E BUSCA
  // ========================================================================

  function filterByCategory(catId) {
    state.activeCategory = catId;
    state.visibleProductCount = PRODUCT_PAGE_SIZE;
    updateCategoryFilterButtons();
    renderProducts();
    renderBusinesses(catId);
  }

  function scrollToProductsSection() {
    const productsSection = document.getElementById('produtos');
    if (productsSection) productsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function updateCategoryFilterButtons() {
    const buttons = document.querySelectorAll('.category-filter-btn');
    buttons.forEach(btn => {
      const cat = btn.getAttribute('data-category');
      if (cat === state.activeCategory) {
        btn.classList.add('bg-[#39BDF5]', 'text-[#030A12]', 'font-bold');
        btn.classList.remove('bg-[#0B1B2A]', 'text-[#B8C7D8]');
      } else {
        btn.classList.remove('bg-[#39BDF5]', 'text-[#030A12]', 'font-bold');
        btn.classList.add('bg-[#0B1B2A]', 'text-[#B8C7D8]');
      }
    });
  }

  // ========================================================================
  // MODAIS E FLUXOS INTERATIVOS
  // ========================================================================

  // Modal Quick View do Produto
  function openQuickViewModal(productId) {
    const product = MARKETPLACE_DATA.products.find(p => p.id === productId);
    if (!product) return;

    const modal = document.getElementById('quick-view-modal');
    if (!modal) return;

    document.getElementById('qv-image').src = getProductImage(product);
    document.getElementById('qv-title').textContent = product.name;
    document.getElementById('qv-business').textContent = product.businessName;
    document.getElementById('qv-rating').textContent = product.rating.toFixed(1);
    document.getElementById('qv-desc').textContent = product.description;
    document.getElementById('qv-price').textContent = formatMoney(product.price);
    
    const origPriceEl = document.getElementById('qv-original-price');
    if (product.originalPrice) {
      origPriceEl.textContent = formatMoney(product.originalPrice);
      origPriceEl.classList.remove('hidden');
    } else {
      origPriceEl.classList.add('hidden');
    }

    const appointmentService = isAppointmentService(product);
    const quantityControl = document.getElementById('qv-qty-control');
    const qtyInput = document.getElementById('qv-qty-input');
    const addLabel = document.getElementById('qv-add-label');
    if (quantityControl) quantityControl.classList.toggle('hidden', appointmentService);
    if (qtyInput) qtyInput.value = '1';
    if (addLabel) addLabel.textContent = appointmentService ? 'Escolher data e horário' : 'Adicionar ao Carrinho';

    const addBtn = document.getElementById('qv-add-btn');
    addBtn.onclick = () => {
      if (appointmentService) {
        closeModal('quick-view-modal');
        showAppointmentModal(product);
        return;
      }
      const qty = parseInt(qtyInput ? qtyInput.value : 1, 10) || 1;
      addToCart(product.id, qty);
      closeModal('quick-view-modal');
    };

    openModal('quick-view-modal');
  }

  // Modal de Parceiro (Empresa)
  function openPartnerModal(preselectedPlan = '') {
    const modal = document.getElementById('partner-modal');
    if (!modal) return;
    const planSelect = document.getElementById('partner-plan-select');
    if (planSelect && preselectedPlan) {
      planSelect.value = preselectedPlan;
    }
    openModal('partner-modal');
  }

  // Modal de Entregador
  function openDriverModal() {
    openModal('driver-modal');
  }

  // Modal de Autenticação (Entrar / Criar Conta)
  function openAuthModal(defaultTab = 'login') {
    switchAuthTab(defaultTab);
    openModal('auth-modal');
  }

  function switchAuthTab(tabName) {
    const tabs = ['login', 'register-client', 'recover'];
    tabs.forEach(t => {
      const panel = document.getElementById(`auth-panel-${t}`);
      const btn = document.getElementById(`auth-tab-${t}`);
      if (panel) {
        if (t === tabName) panel.classList.remove('hidden');
        else panel.classList.add('hidden');
      }
      if (btn) {
        if (t === tabName) {
          btn.classList.add('border-[#39BDF5]', 'text-[#39BDF5]');
          btn.classList.remove('border-transparent', 'text-[#B8C7D8]');
        } else {
          btn.classList.remove('border-[#39BDF5]', 'text-[#39BDF5]');
          btn.classList.add('border-transparent', 'text-[#B8C7D8]');
        }
      }
    });
  }

  // Modal de Checkout
  function openCheckoutModal() {
    if (state.cart.length === 0) {
      showToast('Seu carrinho está vazio!', 'error');
      return;
    }
    const hasDeliveryItems = state.cart.some(item => !item.appointment);
    const addressFields = document.getElementById('checkout-address-fields');
    const stepHeading = document.getElementById('checkout-step1-heading');
    if (addressFields) addressFields.classList.toggle('hidden', !hasDeliveryItems);
    if (stepHeading) {
      stepHeading.lastChild.textContent = hasDeliveryItems
        ? ' Onde devemos entregar seu pedido?'
        : ' Seus dados para confirmar o atendimento';
    }
    closeCartDrawer();
    switchCheckoutStep(1);
    updateCheckoutSummary();
    openModal('checkout-modal');
  }

  function switchCheckoutStep(stepNumber) {
    [1, 2, 3].forEach(s => {
      const panel = document.getElementById(`checkout-step-${s}`);
      const stepIndicator = document.getElementById(`checkout-nav-step-${s}`);
      if (panel) {
        if (s === stepNumber) panel.classList.remove('hidden');
        else panel.classList.add('hidden');
      }
      if (stepIndicator) {
        stepIndicator.classList.remove(
          'border-[#39BDF5]',
          'text-[#39BDF5]',
          'border-emerald-400',
          'text-emerald-400',
          'border-transparent',
          'text-[#6C829D]'
        );

        if (s === stepNumber) {
          stepIndicator.classList.add('border-[#39BDF5]', 'text-[#39BDF5]');
          stepIndicator.setAttribute('aria-current', 'step');
        } else if (s < stepNumber) {
          stepIndicator.classList.add('border-emerald-400', 'text-emerald-400');
          stepIndicator.removeAttribute('aria-current');
        } else {
          stepIndicator.classList.add('border-transparent', 'text-[#6C829D]');
          stepIndicator.removeAttribute('aria-current');
        }
      }
    });
  }

  function updateCheckoutSummary() {
    const { subtotal, deliveryFee, discount, total } = calculateCartTotals();
    const hasDeliveryItems = state.cart.some(item => !item.appointment);
    const subtotalEl = document.getElementById('checkout-subtotal');
    const shippingEl = document.getElementById('checkout-shipping');
    const discountEl = document.getElementById('checkout-discount');
    const totalEl = document.getElementById('checkout-total');
    const appointmentSection = document.getElementById('checkout-appointments');
    const appointmentList = document.getElementById('checkout-appointment-list');

    if (subtotalEl) subtotalEl.textContent = formatMoney(subtotal);
    if (shippingEl) shippingEl.textContent = hasDeliveryItems ? (deliveryFee === 0 ? 'Grátis' : formatMoney(deliveryFee)) : 'Não se aplica';
    if (discountEl) discountEl.textContent = `-${formatMoney(discount)}`;
    if (totalEl) totalEl.textContent = formatMoney(total);
    if (appointmentSection && appointmentList) {
      const appointments = state.cart.filter(item => item.appointment);
      appointmentSection.classList.toggle('hidden', appointments.length === 0);
      appointmentList.innerHTML = appointments.map(item => `
        <li>${escapeHTML(item.product.businessName)} — ${escapeHTML(item.product.name)}: ${formatAppointmentDate(item.appointment.date)} às ${escapeHTML(item.appointment.time)}</li>
      `).join('');
    }
  }

  // Finalização do Pedido e Início da Simulação de Rastreamento
  function completeOrder() {
    const orderNumber = `CON-${Math.floor(100000 + Math.random() * 900000)}`;
    const { total } = calculateCartTotals();

    state.activeOrder = {
      id: orderNumber,
      items: [...state.cart],
      total: total,
      appointmentOnly: state.cart.every(item => Boolean(item.appointment)),
      status: 1, // 1: Confirmado, 2: Preparando, 3: A Caminho, 4: Entregue
      driverName: 'Marcos Vinicius',
      driverVehicle: 'Honda CG 160 Fan - Placa BRA-2E19',
      estimatedTime: '28 minutos'
    };

    const newAppointments = state.cart.filter(item => item.appointment).map(item => ({
      id: item.id || `appointment-${Date.now()}`,
      product: item.product,
      date: item.appointment.date,
      time: item.appointment.time,
      orderId: orderNumber
    }));
    if (newAppointments.length > 0) {
      state.appointments = [...state.appointments, ...newAppointments];
      saveAppointments();
    }

    // Limpa carrinho
    state.cart = [];
    state.appliedCoupon = null;
    updateCartUI();

    closeModal('checkout-modal');
    openOrderTracker(state.activeOrder);
    showToast(state.activeOrder.appointmentOnly
      ? `Agendamento do pedido #${orderNumber} confirmado!`
      : `Pedido #${orderNumber} confirmado com sucesso!`);
  }

  // Rastreador de Pedido em Tempo Real (Visual Stepper)
  function openOrderTracker(order) {
    if (!order) return;

    document.getElementById('tracker-order-id').textContent = `#${order.id}`;
    document.getElementById('tracker-order-total').textContent = formatMoney(order.total);
    document.getElementById('tracker-driver-name').textContent = order.driverName;
    document.getElementById('tracker-driver-vehicle').textContent = order.driverVehicle;
    document.getElementById('tracker-eta').textContent = order.estimatedTime;

    updateTrackerUI(order.status);
    openModal('order-tracker-modal');
  }

  function updateTrackerUI(currentStep) {
    const steps = [
      { id: 1, label: 'Pedido Confirmado', desc: 'Pagamento aprovado instantaneamente' },
      { id: 2, label: 'Em Preparo', desc: 'Estabelecimento parceiro separando seu pedido' },
      { id: 3, label: 'Entregador a Caminho', desc: 'Aproximando-se do seu endereço' },
      { id: 4, label: 'Entregue com Sucesso', desc: 'Pedido finalizado. Bom proveito!' }
    ];

    steps.forEach(s => {
      const circle = document.getElementById(`step-circle-${s.id}`);
      const text = document.getElementById(`step-text-${s.id}`);
      if (!circle || !text) return;

      if (s.id < currentStep) {
        circle.className = 'w-9 h-9 rounded-full bg-emerald-500 text-[#030A12] flex items-center justify-center font-bold text-sm shadow-[0_0_15px_rgba(16,185,129,0.5)]';
        circle.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i>';
        text.className = 'text-xs font-semibold text-emerald-400';
      } else if (s.id === currentStep) {
        circle.className = 'w-9 h-9 rounded-full bg-[#39BDF5] text-[#030A12] flex items-center justify-center font-bold text-sm shadow-[0_0_20px_rgba(57,189,245,0.7)] animate-pulse';
        circle.innerHTML = s.id;
        text.className = 'text-xs font-bold text-[#39BDF5]';
      } else {
        circle.className = 'w-9 h-9 rounded-full bg-[#071522] border border-[#162C46] text-[#6C829D] flex items-center justify-center font-semibold text-sm';
        circle.innerHTML = s.id;
        text.className = 'text-xs text-[#6C829D]';
      }
    });

    if (window.lucide) {
      lucide.createIcons({ root: document.getElementById('tracker-steps-container') });
    }
  }

  // Simulador de progresso do pedido
  function advanceOrderStep() {
    if (!state.activeOrder) return;
    if (state.activeOrder.status < 4) {
      state.activeOrder.status += 1;
      updateTrackerUI(state.activeOrder.status);
      if (state.activeOrder.status === 4) {
        showToast('Pedido entregue com sucesso! Obrigado por comprar no WebService!');
      } else {
        showToast('Status do pedido atualizado!', 'info');
      }
    }
  }

  // Utilitários de Modais Gerais
  function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
      if (window.lucide) lucide.createIcons({ root: modal });
    }
  }

  function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.add('hidden');
      document.body.style.overflow = '';
    }
  }

  function openCartDrawer() {
    closeAppointmentsDrawer();
    const drawer = document.getElementById('cart-drawer');
    const overlay = document.getElementById('cart-overlay');
    if (drawer && overlay) {
      overlay.classList.remove('hidden');
      setTimeout(() => drawer.classList.remove('translate-x-full'), 10);
      document.body.style.overflow = 'hidden';
      updateCartUI();
    }
  }

  function closeCartDrawer() {
    const drawer = document.getElementById('cart-drawer');
    const overlay = document.getElementById('cart-overlay');
    if (drawer && overlay) {
      drawer.classList.add('translate-x-full');
      setTimeout(() => overlay.classList.add('hidden'), 300);
      document.body.style.overflow = '';
    }
  }

  function openAppointmentsDrawer() {
    closeCartDrawer();
    const drawer = document.getElementById('appointments-drawer');
    const overlay = document.getElementById('appointments-overlay');
    if (drawer && overlay) {
      updateAppointmentsUI();
      overlay.classList.remove('hidden');
      setTimeout(() => drawer.classList.remove('translate-x-full'), 10);
      document.body.style.overflow = 'hidden';
    }
  }

  function closeAppointmentsDrawer() {
    const drawer = document.getElementById('appointments-drawer');
    const overlay = document.getElementById('appointments-overlay');
    if (drawer && overlay) {
      drawer.classList.add('translate-x-full');
      setTimeout(() => overlay.classList.add('hidden'), 300);
      document.body.style.overflow = '';
    }
  }

  // ========================================================================
  // BINDING DE EVENTOS GLOBAIS
  // ========================================================================

  function setupEventListeners() {
    // 1. Busca em Tempo Real no Header e Hero
    const searchInputs = [
      document.getElementById('nav-search-input'),
      document.getElementById('hero-search-input'),
      document.getElementById('products-search-input')
    ];

    searchInputs.forEach(input => {
      if (!input) return;
      input.addEventListener('input', (e) => {
        state.searchQuery = e.target.value;
        state.visibleProductCount = PRODUCT_PAGE_SIZE;
        // Sincroniza os outros inputs
        searchInputs.forEach(other => {
          if (other && other !== input) other.value = e.target.value;
        });
        renderProducts();
      });

      input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          const prodSection = document.getElementById('produtos');
          if (prodSection) prodSection.scrollIntoView({ behavior: 'smooth' });
        }
      });
    });

    // 2. Filtros de Categoria em Botões Pills
    document.querySelectorAll('.category-filter-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const cat = btn.getAttribute('data-category');
        state.activeCategory = cat;
        state.visibleProductCount = PRODUCT_PAGE_SIZE;
        updateCategoryFilterButtons();
        renderProducts();
        scrollToProductsSection();
      });
    });

    // 3. Filtro por Faixa de Preço
    const priceFilterSelect = document.getElementById('price-filter-select');
    if (priceFilterSelect) {
      priceFilterSelect.addEventListener('change', (e) => {
        state.priceFilter = e.target.value;
        state.visibleProductCount = PRODUCT_PAGE_SIZE;
        renderProducts();
        scrollToProductsSection();
      });
    }

    // 4. Carrinho Drawer Triggers
    document.querySelectorAll('.btn-open-cart').forEach(b => {
      b.addEventListener('click', openCartDrawer);
    });
    const btnCloseCart = document.getElementById('btn-close-cart');
    if (btnCloseCart) btnCloseCart.addEventListener('click', closeCartDrawer);
    const cartOverlay = document.getElementById('cart-overlay');
    if (cartOverlay) cartOverlay.addEventListener('click', closeCartDrawer);
    document.querySelectorAll('.btn-open-appointments').forEach(button => {
      button.addEventListener('click', openAppointmentsDrawer);
    });
    const closeAppointmentsButton = document.getElementById('btn-close-appointments');
    if (closeAppointmentsButton) closeAppointmentsButton.addEventListener('click', closeAppointmentsDrawer);
    const appointmentsOverlay = document.getElementById('appointments-overlay');
    if (appointmentsOverlay) appointmentsOverlay.addEventListener('click', closeAppointmentsDrawer);

    // 5. Cupom de Desconto no Carrinho
    const btnApplyCoupon = document.getElementById('btn-apply-coupon');
    const couponInput = document.getElementById('coupon-input');
    if (btnApplyCoupon && couponInput) {
      btnApplyCoupon.addEventListener('click', () => {
        const code = couponInput.value.trim().toUpperCase();
        if (AVAILABLE_COUPONS[code]) {
          state.appliedCoupon = AVAILABLE_COUPONS[code];
          showToast(`Cupom "${code}" aplicado com sucesso!`);
          updateCartUI();
        } else {
          showToast('Cupom inválido ou expirado. Tente WEBSERVICE10', 'error');
        }
      });
    }

    // 6. Botão de Finalizar no Carrinho -> Abre Checkout
    const btnGoToCheckout = document.getElementById('btn-go-to-checkout');
    if (btnGoToCheckout) {
      btnGoToCheckout.addEventListener('click', openCheckoutModal);
    }

    // 7. Navegação no Checkout
    const btnNextStep1 = document.getElementById('checkout-next-1');
    if (btnNextStep1) {
      btnNextStep1.addEventListener('click', () => {
        // Validação simples de campos obrigatórios
        const name = document.getElementById('checkout-name').value.trim();
        const phone = document.getElementById('checkout-phone').value.trim();
        const cep = document.getElementById('checkout-cep').value.trim();
        const street = document.getElementById('checkout-street').value.trim();
        const number = document.getElementById('checkout-number').value.trim();
        const hasDeliveryItems = state.cart.some(item => !item.appointment);

        if (!name || !phone || (hasDeliveryItems && (!cep || !street || !number))) {
          showToast(hasDeliveryItems
            ? 'Preencha seus dados e os campos obrigatórios de endereço.'
            : 'Preencha seu nome e telefone para confirmar o atendimento.', 'error');
          return;
        }
        switchCheckoutStep(2);
      });
    }

    const btnBackStep2 = document.getElementById('checkout-back-2');
    if (btnBackStep2) {
      btnBackStep2.addEventListener('click', () => switchCheckoutStep(1));
    }

    const btnNextStep2 = document.getElementById('checkout-next-2');
    if (btnNextStep2) {
      btnNextStep2.addEventListener('click', () => switchCheckoutStep(3));
    }

    const btnBackStep3 = document.getElementById('checkout-back-3');
    if (btnBackStep3) {
      btnBackStep3.addEventListener('click', () => switchCheckoutStep(2));
    }

    const btnConfirmOrder = document.getElementById('btn-confirm-order');
    if (btnConfirmOrder) {
      btnConfirmOrder.addEventListener('click', completeOrder);
    }

    const appointmentDate = document.getElementById('appointment-date');
    if (appointmentDate) {
      appointmentDate.addEventListener('change', populateAppointmentTimes);
    }

    const appointmentForm = document.getElementById('appointment-form');
    if (appointmentForm) {
      appointmentForm.addEventListener('submit', event => {
        event.preventDefault();
        const pending = state.pendingAppointment;
        const date = appointmentDate && appointmentDate.value;
        const time = document.getElementById('appointment-time').value;
        const product = pending && MARKETPLACE_DATA.products.find(item => item.id === pending.productId);
        const availableTimes = getAvailableAppointmentTimes(date, pending && pending.businessId);

        if (!pending || !product || !date || date < getLocalDateValue(new Date()) || !time || !availableTimes.includes(time)) {
          showToast('Selecione uma data e um horário disponível para o serviço.', 'error');
          return;
        }

        addToCart(product.id, pending.quantity, { date, time });
        state.pendingAppointment = null;
        closeModal('appointment-modal');
      });
    }

    // 8. Auto-completar CEP Simulado
    const cepInput = document.getElementById('checkout-cep');
    if (cepInput) {
      cepInput.addEventListener('blur', () => {
        const cleanCep = cepInput.value.replace(/\D/g, '');
        if (cleanCep.length === 8) {
          // Preenche automaticamente com dados brasileiros
          document.getElementById('checkout-street').value = 'Av. Paulista';
          document.getElementById('checkout-neighborhood').value = 'Bela Vista';
          document.getElementById('checkout-city').value = 'São Paulo';
          document.getElementById('checkout-state').value = 'SP';
          showToast('Endereço preenchido automaticamente pelo CEP!');
        }
      });
    }

    // 9. Alternância de Método de Pagamento no Checkout
    document.querySelectorAll('.payment-method-option').forEach(opt => {
      opt.addEventListener('click', () => {
        document.querySelectorAll('.payment-method-option').forEach(o => {
          o.classList.remove('border-[#39BDF5]', 'bg-[#39BDF5]/10');
          o.classList.add('border-[#162C46]');
        });
        opt.classList.add('border-[#39BDF5]', 'bg-[#39BDF5]/10');
        opt.classList.remove('border-[#162C46]');

        const method = opt.getAttribute('data-payment-method');
        const pixBox = document.getElementById('pix-payment-box');
        const cardBox = document.getElementById('card-payment-box');
        if (method === 'pix') {
          if (pixBox) pixBox.classList.remove('hidden');
          if (cardBox) cardBox.classList.add('hidden');
        } else {
          if (pixBox) pixBox.classList.add('hidden');
          if (cardBox) cardBox.classList.remove('hidden');
        }
      });
    });

    // 10. Botão Copiar Chave Pix
    const btnCopyPix = document.getElementById('btn-copy-pix');
    if (btnCopyPix) {
      btnCopyPix.addEventListener('click', () => {
        const pixCode = document.getElementById('pix-code-text').textContent;
        navigator.clipboard?.writeText(pixCode).then(() => {
          showToast('Código Pix copiado para a área de transferência!');
        }).catch(() => {
          showToast('Código Pix copiado!');
        });
      });
    }

    // 11. Fechar Modais via Botão X ou Fundo
    document.querySelectorAll('.btn-close-modal').forEach(b => {
      b.addEventListener('click', () => {
        const modalId = b.getAttribute('data-modal-id');
        closeModal(modalId);
      });
    });

    // 12. Botões de Registro / Abertura de Modais Específicos
    document.querySelectorAll('.btn-open-auth').forEach(b => {
      b.addEventListener('click', () => {
        const tab = b.getAttribute('data-tab') || 'login';
        openAuthModal(tab);
      });
    });

    document.querySelectorAll('.btn-open-partner').forEach(b => {
      b.addEventListener('click', () => openPartnerModal());
    });

    document.querySelectorAll('.btn-open-driver').forEach(b => {
      b.addEventListener('click', openDriverModal);
    });

    // 13. Formulário de Parceria (Empresas)
    const partnerForm = document.getElementById('partner-form');
    if (partnerForm) {
      partnerForm.addEventListener('submit', (e) => {
        e.preventDefault();
        showToast('Inscrição de parceiro enviada! Nossa equipe entrará em contato em até 24h.');
        partnerForm.reset();
        closeModal('partner-modal');
      });
    }

    const managerBusinessForm = document.getElementById('manager-business-form');
    if (managerBusinessForm) {
      managerBusinessForm.addEventListener('submit', createBusiness);
    }
    const managerProductForm = document.getElementById('manager-product-form');
    if (managerProductForm) {
      managerProductForm.addEventListener('submit', createProduct);
    }
    // 14. Formulário de Entregador
    const driverForm = document.getElementById('driver-form');
    if (driverForm) {
      driverForm.addEventListener('submit', (e) => {
        e.preventDefault();
        showToast('Cadastro de entregador recebido! Enviaremos a confirmação por WhatsApp.');
        driverForm.reset();
        closeModal('driver-modal');
      });
    }

    // 15. Formulário de Contato
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
      contactForm.addEventListener('submit', (e) => {
        e.preventDefault();
        showToast('Mensagem enviada com sucesso! Responderemos em breve.');
        contactForm.reset();
      });
    }

    // 16. Formulário de Newsletter
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
      newsletterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        showToast('Inscrição confirmada! Você receberá nossas ofertas e cupons exclusivos.');
        newsletterForm.reset();
      });
    }

    // 17. Botão do Rastreador de Pedidos: Avançar Etapa
    const btnAdvanceOrder = document.getElementById('btn-advance-order');
    if (btnAdvanceOrder) {
      btnAdvanceOrder.addEventListener('click', advanceOrderStep);
    }

    // 18. Chat de Suporte Flutuante
    const chatToggle = document.getElementById('chat-widget-toggle');
    const chatWindow = document.getElementById('chat-widget-window');
    const chatClose = document.getElementById('chat-widget-close');
    const chatForm = document.getElementById('chat-widget-form');
    const chatInput = document.getElementById('chat-widget-input');
    const chatMessages = document.getElementById('chat-widget-messages');

    if (chatToggle && chatWindow) {
      chatToggle.addEventListener('click', () => {
        chatWindow.classList.toggle('hidden');
      });
      if (chatClose) {
        chatClose.addEventListener('click', () => chatWindow.classList.add('hidden'));
      }
    }

    if (chatForm && chatInput && chatMessages) {
      let lastFallbackIndex = -1;
      chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const text = chatInput.value.trim();
        if (!text) return;

        // Mensagem do usuário
        const userMsg = document.createElement('div');
        userMsg.className = 'text-right my-2';
        const userBubble = document.createElement('div');
        userBubble.className = 'inline-block bg-[#087BD4] text-white text-xs px-3.5 py-2 rounded-2xl rounded-tr-none max-w-[85%] text-left';
        userBubble.textContent = text;
        userMsg.appendChild(userBubble);
        chatMessages.appendChild(userMsg);
        chatInput.value = '';
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Resposta inteligente automática em Português
        const normalizedText = text.toLocaleLowerCase('pt-BR').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        setTimeout(() => {
          let reply;
          const lower = normalizedText;
          if (lower.includes('rastrear') || lower.includes('pedido') || lower.includes('onde esta')) {
            reply = 'Para rastrear seu pedido, clique no ícone "Meus Pedidos" ou utilize o código recebido no WhatsApp/E-mail.';
          } else if (lower.includes('parceiro') || lower.includes('empresa') || lower.includes('vender')) {
            reply = 'Para cadastrar lojas e produtos, abra "Painel Loja" no topo da página. Você poderá adicionar lojas, cadastrar produtos e remover itens do catálogo.';
          } else if (lower.includes('produto') || lower.includes('item') || lower.includes('catalogo') || lower.includes('loja')) {
            reply = 'No "Painel Loja", cadastre uma loja e depois escolha-a no formulário de produto para incluir itens com preço, descrição e imagem.';
          } else if (lower.includes('entregador') || lower.includes('entrega') || lower.includes('trabalhar')) {
            reply = 'Entregadores autônomos podem se registrar na aba "Entregadores" enviando CNH e dados do veículo.';
          } else if (lower.includes('pix') || lower.includes('pagamento') || lower.includes('cartao')) {
            reply = 'Aceitamos pagamentos instantâneos via Pix com aprovação em segundos, além de cartões de crédito e débito.';
          } else if (/\b(bom dia|boa tarde|boa noite|ola|oi|e ai)\b/.test(lower)) {
            reply = 'Olá! Posso ajudar com pedidos, pagamentos, entregas ou com o cadastro de lojas e produtos. O que você precisa?';
          } else {
            const fallbackReplies = [
              'Ainda não tenho essa informação. Posso orientar sobre pedidos, pagamentos, entregas e cadastro de lojas.',
              'Não encontrei uma resposta para isso. Você pode perguntar sobre rastreamento, formas de pagamento ou como adicionar produtos.',
              'Posso ajudar com o catálogo e os pedidos do WebService. Qual desses assuntos você quer consultar?'
            ];
            lastFallbackIndex = (lastFallbackIndex + 1) % fallbackReplies.length;
            reply = fallbackReplies[lastFallbackIndex];
          }

          const botMsg = document.createElement('div');
          botMsg.className = 'text-left my-2';
          const botBubble = document.createElement('div');
          botBubble.className = 'inline-block bg-[#071522] border border-[#162C46] text-[#B8C7D8] text-xs px-3.5 py-2 rounded-2xl rounded-tl-none max-w-[85%]';
          botBubble.textContent = reply;
          botMsg.appendChild(botBubble);
          chatMessages.appendChild(botMsg);
          chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 800);
      });
    }

    // 19. Menu Mobile Toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu-drawer');
    const mobileMenuClose = document.getElementById('mobile-menu-close');
    if (mobileMenuBtn && mobileMenu) {
      mobileMenuBtn.addEventListener('click', () => {
        mobileMenu.classList.remove('hidden');
      });
      if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', () => mobileMenu.classList.add('hidden'));
      }
      mobileMenu.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', () => mobileMenu.classList.add('hidden'));
      });
    }

    // 20. Role Switcher Modal (Visualização de Papéis do Business Model Canvas)
    document.querySelectorAll('.btn-role-preview').forEach(btn => {
      btn.addEventListener('click', () => {
        const role = btn.getAttribute('data-role');
        if (role === 'partner') {
          openModal('role-partner-preview-modal');
        } else if (role === 'driver') {
          openModal('role-driver-preview-modal');
        } else {
          showToast('Você está navegando na visão principal de Cliente!', 'info');
        }
      });
    });

    // 21. Navegação Ativa com Traço Azul e Transição Suave em Todos os Atalhos
    function setupNavigationSpy() {
      const desktopLinks = document.querySelectorAll('nav .nav-item-link');
      const mobileLinks = document.querySelectorAll('#mobile-menu-drawer .mobile-nav-link');
      const sections = [
        document.getElementById('inicio'),
        document.getElementById('como-funciona'),
        document.getElementById('categorias'),
        document.getElementById('empresas'),
        document.getElementById('entregadores'),
        document.getElementById('suporte')
      ].filter(Boolean);

      function setActiveLink(targetId) {
        desktopLinks.forEach(link => {
          const href = link.getAttribute('href');
          if (href === `#${targetId}`) {
            link.classList.add('active');
          } else {
            link.classList.remove('active');
          }
        });

        mobileLinks.forEach(link => {
          const href = link.getAttribute('href');
          if (href === `#${targetId}`) {
            link.classList.add('text-[#39BDF5]', 'font-bold');
            link.classList.remove('text-[#B8C7D8]');
          } else {
            link.classList.remove('text-[#39BDF5]', 'font-bold');
            link.classList.add('text-[#B8C7D8]');
          }
        });
      }

      desktopLinks.forEach(link => {
        link.addEventListener('click', (e) => {
          const href = link.getAttribute('href');
          if (href && href.startsWith('#')) {
            setActiveLink(href.substring(1));
          }
        });
      });

      mobileLinks.forEach(link => {
        link.addEventListener('click', (e) => {
          const href = link.getAttribute('href');
          if (href && href.startsWith('#')) {
            setActiveLink(href.substring(1));
          }
        });
      });

      // Detecção dinâmica durante o scroll da página
      let isTicking = false;
      window.addEventListener('scroll', () => {
        if (!isTicking) {
          window.requestAnimationFrame(() => {
            const scrollPos = window.scrollY + 180;
            let currentId = 'inicio';

            sections.forEach(sec => {
              const top = sec.offsetTop;
              const height = sec.offsetHeight;
              if (scrollPos >= top && scrollPos < top + height) {
                currentId = sec.getAttribute('id');
              }
            });

            // Se chegou ao fim da página, ativa o Contato/Suporte
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 80) {
              currentId = 'suporte';
            }

            setActiveLink(currentId);
            isTicking = false;
          });
          isTicking = true;
        }
      }, { passive: true });
    }

    setupNavigationSpy();
    function simulateOrderTracking() {
      state.activeOrder = {
        id: 'DEMO-8941',
        items: [],
        total: 64.90,
        status: 2,
        driverName: 'Marcos Vinicius',
        driverVehicle: 'Honda CG 160 Fan - Placa BRA-2E19',
        estimatedTime: '18 minutos'
      };
      openOrderTracker(state.activeOrder);
      showToast('Simulação iniciada! O entregador está se deslocando.', 'info');
    }

    // Expõe funções para uso global/inline no HTML
    window.switchAuthTab = switchAuthTab;
    window.openModal = openModal;
    window.closeModal = closeModal;
    window.openCartDrawer = openCartDrawer;
    window.closeCartDrawer = closeCartDrawer;
    window.openAppointmentsDrawer = openAppointmentsDrawer;
    window.closeAppointmentsDrawer = closeAppointmentsDrawer;
    window.openPartnerModal = openPartnerModal;
    window.openDriverModal = openDriverModal;
    window.openAuthModal = openAuthModal;
    window.openOrderTracker = openOrderTracker;
    window.openQuickViewModal = openQuickViewModal;
    window.addToCart = addToCart;
    window.showToast = showToast;
    window.filterByCategory = filterByCategory;
    window.advanceOrderStep = advanceOrderStep;
    window.appState = state;
    window.completeOrder = completeOrder;
    window.simulateOrderTracking = simulateOrderTracking;
  }

  // ========================================================================
  // INICIALIZAÇÃO GERAL
  // ========================================================================
  function init() {
    loadSavedCatalog();
    loadSavedAppointments();
    ensureUniqueProductImages();
    renderCategories();
    renderBusinesses();
    renderProducts();
    renderManagerCatalog();
    renderPartnerPlans();
    renderFaq();
    updateCartUI();
    setupEventListeners();

    if (window.lucide) {
      lucide.createIcons();
    }
  }

  init();
});

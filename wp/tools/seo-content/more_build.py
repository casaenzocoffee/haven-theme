#!/usr/bin/env python3
"""Bloques extra ("more") para páginas Haven que tenían poco texto (auditoría SEO, 5 nov 2026).
Genera more.json: {clave: {es: [bloques], en: [bloques]}}. Mismos tipos de bloque que la plantilla landing
(text con sub/h3, rooms, faq, links, cta). Solo hechos tomados de las cartas, la FAQ y las fichas; trato de "usted".
Se aplica guardando _hv_data['more'] de cada página (panel "Contenido Haven" → JSON) o con seo-content.php."""
import json, os

WA = '+57 321 209 7033'
M = {}

# ---------------------------------------------------------------- SWING LOUNGE
M['swing'] = {
 'es': [
  {'type': 'text', 'eyebrow': 'El café de la casa', 'title': 'Café de especialidad de Antioquia, <em>en el vestíbulo</em>',
   'paras': [
    'Swing Lounge es el café de Haven, A Design Hotel: está en el vestíbulo, en la Calle 10A #36-29 de El Poblado, y abre todos los días de 8:00 a. m. a 6:00 p. m. Está abierto a huéspedes y a visitantes, sin reserva.',
    'Es el lugar para el primer espresso del día antes de salir a recorrer Medellín, para una reunión corta, para trabajar un rato con buen café al lado o para hacer una pausa en la tarde antes de volver a Provenza, que queda a 2 minutos a pie.'],
   'sub': [
    {'h3': 'El grano', 'paras': [
      'Trabajamos con café de especialidad de Antioquia en cuatro variedades: Caturra, Bourbon Rosado, Tabi Amarillo y Honey. Nuestro café insignia es Casa Enzo Coffee, cultivado por caficultores colombianos y tostado en Medellín.',
      'Su primer lote, Primera Luz, es un Caturra cosechado a mano a 1.699 metros en las montañas de Santa Elena, sobre la ciudad: un microlote artesanal con notas de manzana verde, frutos amarillos y caña de azúcar. Es el mismo café del desayuno y de las habitaciones, y se puede llevar de regalo.']},
    {'h3': 'Qué pedir', 'paras': [
      'De la barra de café: capuchino, café latte, americano, espresso doble, mocachino y affogato. Para los días calurosos, un latte frío. Si prefiere algo distinto al café, hay matcha batido con leche, chocolate caliente con cacao orgánico, té y aromáticas.',
      'Para acompañar: brownie tibio con helado de vainilla, un batido de proteína con banano, avena, almendras y cacao, o una limonada de coco. Y para la tarde, un carajillo 43, caliente o frío, una margarita o un gin & tonic con pepino y cítricos.']},
    {'h3': 'Para quién es', 'paras': [
      'Para el huésped que baja temprano y quiere un buen café sin salir del hotel; para quien llega a una reunión en El Poblado y busca un lugar tranquilo; y para el visitante que recorre Provenza y quiere probar café de especialidad de Antioquia en un espacio de diseño. En la carta digital cada bebida indica sus alérgenos.']}
   ]},
  {'type': 'faq', 'items': [
    {'q': '¿Hay que reservar para ir a Swing Lounge?', 'a': 'No. Swing Lounge está abierto a huéspedes y visitantes, sin reserva.'},
    {'q': '¿Cuál es el horario de Swing Lounge?', 'a': 'Abre todos los días, de lunes a domingo, de 8:00 a. m. a 6:00 p. m.'},
    {'q': '¿Qué café sirven?', 'a': 'Café de especialidad de Antioquia en variedades Caturra, Bourbon Rosado, Tabi Amarillo y Honey. Nuestro café insignia es Casa Enzo Coffee, con su lote Primera Luz, un Caturra de Santa Elena cultivado a 1.699 metros.'},
    {'q': '¿Puedo comprar café para llevar a casa?', 'a': 'Sí. Una bolsa de Casa Enzo Coffee se puede llevar como regalo; pregunte en Swing Lounge.'},
    {'q': '¿Dónde queda Swing Lounge?', 'a': 'En el vestíbulo de Haven, A Design Hotel, Calle 10A #36-29, El Poblado, a 2 minutos a pie de Provenza.'},
    {'q': '¿Hay opciones sin café?', 'a': 'Sí: matcha, chocolate caliente con cacao orgánico, té y aromáticas, jugos de naranja, maracuyá, mango y mora, limonada natural y limonada de coco.'}]},
  {'type': 'links', 'title': 'Siga el plan <em>en Haven</em>', 'keys': ['rooftop', 'gastronomia', 'guia-cafes', 'guia-a-pie']},
 ],
 'en': [
  {'type': 'text', 'eyebrow': 'Our house café', 'title': 'Antioquia specialty coffee, <em>in the lobby</em>',
   'paras': [
    'Swing Lounge is the café at Haven, A Design Hotel. It sits in the lobby, at Calle 10A #36-29 in El Poblado, and opens every day from 8:00 a.m. to 6:00 p.m. It is open to guests and visitors, no reservation needed.',
    'It is the place for the first espresso of the day before heading out into Medellín, for a short meeting, for a stretch of work with good coffee at hand, or for an afternoon pause before going back to Provenza, a two-minute walk away.'],
   'sub': [
    {'h3': 'The beans', 'paras': [
      'We serve Antioquia specialty coffee in four varieties: Caturra, Pink Bourbon, Yellow Tabi and Honey. Our signature coffee is Casa Enzo Coffee, grown by Colombian coffee farmers and roasted in Medellín.',
      'Its first lot, Primera Luz, is a Caturra hand-picked at 1,699 meters in the mountains of Santa Elena, above the city: an artisanal micro-lot with notes of green apple, yellow fruits and sugarcane. It is the same coffee we serve at breakfast and in the rooms, and you can take a bag home as a gift.']},
    {'h3': 'What to order', 'paras': [
      'From the coffee bar: cappuccino, latte, americano, double espresso, mocaccino and affogato. On warm days, an iced latte. If you would rather skip coffee, there is matcha whisked with milk, hot chocolate made with organic cocoa, tea and infusions.',
      'On the side: a warm brownie with vanilla ice cream, a protein shake with banana, oats, almonds and cocoa, or a coconut lemonade. And for the afternoon, a Carajillo 43, hot or cold, a margarita or a gin & tonic with cucumber and citrus.']},
    {'h3': 'Who it is for', 'paras': [
      'For the guest who comes down early and wants great coffee without leaving the hotel; for anyone with a meeting in El Poblado who needs a quiet spot; and for visitors exploring Provenza who want to taste Antioquia specialty coffee in a design setting. The digital menu lists the allergens of every drink.']}
   ]},
  {'type': 'faq', 'items': [
    {'q': 'Do I need a reservation for Swing Lounge?', 'a': 'No. Swing Lounge is open to guests and visitors, no reservation needed.'},
    {'q': 'What are Swing Lounge opening hours?', 'a': 'It is open every day, Monday to Sunday, from 8:00 a.m. to 6:00 p.m.'},
    {'q': 'What coffee do you serve?', 'a': 'Antioquia specialty coffee in Caturra, Pink Bourbon, Yellow Tabi and Honey varieties. Our signature coffee is Casa Enzo Coffee and its Primera Luz lot, a Caturra from Santa Elena grown at 1,699 meters.'},
    {'q': 'Can I buy coffee to take home?', 'a': 'Yes. You can take a bag of Casa Enzo Coffee home as a gift; just ask at Swing Lounge.'},
    {'q': 'Where is Swing Lounge?', 'a': 'In the lobby of Haven, A Design Hotel, Calle 10A #36-29, El Poblado, a two-minute walk from Provenza.'},
    {'q': 'Are there options without coffee?', 'a': 'Yes: matcha, hot chocolate with organic cocoa, tea and infusions, orange, passion fruit, mango and blackberry juices, fresh lemonade and coconut lemonade.'}]},
  {'type': 'links', 'title': 'Keep the plan <em>at Haven</em>', 'keys': ['rooftop', 'gastronomia', 'guia-cafes', 'guia-a-pie']},
 ]}

# ---------------------------------------------------------------- WELLNESS
M['wellness'] = {
 'es': [
  {'type': 'text', 'eyebrow': 'Bienestar en Haven', 'title': 'Recuperarse del viaje <em>sin salir del hotel</em>',
   'paras': [
    'Los servicios de wellness de Haven se hacen en alianza con Pasitea Spa y se agendan con nuestro equipo. Están pensados para lo que más se nota en un viaje: el cansancio del vuelo, la piel deshidratada y la tensión acumulada después de un día entero recorriendo Medellín.',
    'Puede reservarlos escribiéndonos por WhatsApp al ' + WA + ' o en la recepción, que atiende las 24 horas. Al agendar le confirmamos el horario y el lugar del servicio, y le enviamos el portafolio completo de Pasitea Spa.'],
   'sub': [
    {'h3': 'Masaje relajante: 1 hora o 1 hora y media', 'paras': [
      'Estimula la circulación y libera la tensión de los músculos. Es el servicio indicado después de un vuelo largo o al final de un día de caminatas por la ciudad, antes de subir al 360° Rooftop o de cenar en Provenza.']},
    {'h3': 'Hidratación facial: 40 minutos', 'paras': [
      'Un ritual facial de Pasitea Spa para nutrir la piel y combatir la sequedad y el cansancio. Cuarenta minutos que se notan de inmediato, ideales antes de una celebración o una noche especial.']},
    {'h3': 'Terapia de sueros Ultra Vit', 'paras': [
      'Repone la hidratación y aporta un impulso de energía, pensada para el jet lag y el cansancio del viaje. Al agendarla le explicamos cómo se aplica y le confirmamos el horario.']},
    {'h3': 'Cómo combinarlos', 'paras': [
      'Si llega de un vuelo largo, la terapia de sueros y un masaje en el mismo día ayudan a arrancar el viaje con energía. Si viene en pareja, puede sumar el wellness a una habitación con jacuzzi privado, como la Viscount 803, que tiene el jacuzzi en el balcón. Y si prefiere un plan de agua y sol, la piscina climatizada del piso 11 está incluida para los huéspedes.']}
   ]},
  {'type': 'faq', 'items': [
    {'q': '¿Cómo reservo un masaje o un tratamiento?', 'a': 'Escríbanos por WhatsApp al ' + WA + ' o pídalo en la recepción, que atiende las 24 horas. Le confirmamos horario y lugar.'},
    {'q': '¿Cuánto dura el masaje relajante?', 'a': 'Hay sesiones de 1 hora o de 1 hora y media.'},
    {'q': '¿Cuánto dura la hidratación facial?', 'a': 'Unos 40 minutos.'},
    {'q': '¿Quién presta los servicios de wellness?', 'a': 'Los masajes y tratamientos se hacen en alianza con Pasitea Spa, y nuestro equipo coordina la reserva.'},
    {'q': '¿Puedo combinar varios servicios?', 'a': 'Sí. Cuéntenos al escribir qué le interesa y armamos los horarios para que pueda hacerlos el mismo día.'}]},
  {'type': 'links', 'title': 'Para completar <em>su estadía</em>', 'keys': ['suites-de-lujo', 'escapada-romantica', 'dia-de-sol', 'servicios']},
 ],
 'en': [
  {'type': 'text', 'eyebrow': 'Wellness at Haven', 'title': 'Recover from the trip <em>without leaving the hotel</em>',
   'paras': [
    'Wellness at Haven is offered in partnership with Pasitea Spa and booked through our team. It is designed for what you feel most on a trip: flight fatigue, dehydrated skin and the tension that builds up after a full day exploring Medellín.',
    'Book by WhatsApp at ' + WA + ' or at the front desk, open 24 hours. When you book, we confirm the time and place of the treatment and send you Pasitea Spa\'s full portfolio.'],
   'sub': [
    {'h3': 'Relaxing massage: 1 hour or 1.5 hours', 'paras': [
      'It boosts circulation and releases muscle tension. It is the right choice after a long flight or at the end of a day walking the city, before heading up to the 360° Rooftop or out to dinner in Provenza.']},
    {'h3': 'Facial hydration: 40 minutes', 'paras': [
      'A Pasitea Spa facial ritual that nourishes the skin and fights dryness and tiredness. Forty minutes you notice right away, ideal before a celebration or a special night out.']},
    {'h3': 'Ultra Vit IV therapy', 'paras': [
      'It restores hydration and gives you an energy boost, designed for jet lag and travel fatigue. When you book, we explain how it is given and confirm the time.']},
    {'h3': 'How to combine them', 'paras': [
      'If you arrive from a long flight, IV therapy and a massage on the same day help you start the trip with energy. Traveling as a couple? Pair wellness with a room with a private jacuzzi, such as the Viscount 803, whose jacuzzi is on the balcony. And if you would rather enjoy sun and water, the heated pool on the 11th floor is included for guests.']}
   ]},
  {'type': 'faq', 'items': [
    {'q': 'How do I book a massage or treatment?', 'a': 'Message us on WhatsApp at ' + WA + ' or ask at the front desk, open 24 hours. We confirm the time and place.'},
    {'q': 'How long is the relaxing massage?', 'a': 'Sessions are 1 hour or 1.5 hours.'},
    {'q': 'How long is the facial hydration?', 'a': 'About 40 minutes.'},
    {'q': 'Who provides the wellness treatments?', 'a': 'Massages and treatments are offered in partnership with Pasitea Spa, and our team handles the booking.'},
    {'q': 'Can I combine several treatments?', 'a': 'Yes. Tell us what you are interested in and we will schedule them so you can do them on the same day.'}]},
  {'type': 'links', 'title': 'To round out <em>your stay</em>', 'keys': ['suites-de-lujo', 'escapada-romantica', 'dia-de-sol', 'servicios']},
 ]}

# ---------------------------------------------------------------- GASTRONOMÍA
HOR_ES = 'lunes a miércoles de 7:00 a. m. a 11:00 p. m.; jueves de 7:00 a. m. a 12:00 a. m.; viernes de 7:00 a. m. a 2:00 a. m.; sábado de 8:00 a. m. a 2:00 a. m., y domingo de 8:00 a. m. a 12:00 a. m. (hasta las 2:00 a. m. si el lunes es festivo)'
HOR_EN = 'Monday to Wednesday 7:00 a.m.–11:00 p.m.; Thursday 7:00 a.m.–midnight; Friday 7:00 a.m.–2:00 a.m.; Saturday 8:00 a.m.–2:00 a.m.; and Sunday 8:00 a.m.–midnight (until 2:00 a.m. before a public holiday)'
M['gastronomia'] = {
 'es': [
  {'type': 'text', 'eyebrow': 'La carta del piso 11', 'title': 'Cocina local e internacional, <em>de la mañana a la noche</em>',
   'paras': [
    'La cocina de Haven está en el 360° Rooftop & Restaurante, en el piso 11, con vista de 360° de Medellín. La carta mezcla cocina local e internacional, coctelería de autor y una barra completa, y está abierta a huéspedes y visitantes.',
    'El horario es ' + HOR_ES + '. Puede reservar mesa en línea o por WhatsApp al ' + WA + '.'],
   'sub': [
    {'h3': 'Desayunos todo el día', 'paras': [
      'Huevos gourmet en cacerola con pan focaccia, omelette con mozzarella, el Típico Medallo con frijoles como homenaje a la tradición paisa, bowls de yogur griego o de açaí con fruta, y sopas de pollo o de lentejas. Los huéspedes tienen el desayuno bufé incluido en la tarifa.']},
    {'h3': 'Para compartir', 'paras': [
      'Ceviche de tilapia, wontons de cangrejo, empanaditas, alitas, el Doble Crocante de chicharrón con patacones, mazorcas laqueadas y una picada de carnes para tres. Las pizzas son de 8 porciones: Margarita, Pepperoni, Pollo y champiñones, y La Culpable, con pollo, pepperoni y piña.']},
    {'h3': 'Platos fuertes', 'paras': [
      'Solomitos de 250 gramos en salsa de pimienta, endiablado o con ricotta y espinaca; lomo saltado; costillas BBQ de 500 gramos; salmón a la croûte o con ricotta y espinaca; pulpo a la gallega; paella marinera para dos, risotto y chaufa de mariscos. Para algo informal, la Smash Burger o la Trilogía de hamburguesas.']},
    {'h3': 'Coctelería de autor', 'paras': [
      'Los cócteles de la casa son el sello del rooftop: Watermelon Mezcal, Don Julio Garden, Havenly Haven, Catrina y Medallo, nuestra versión del Moscow Mule. Hay margaritas con Don Julio y Patrón, clásicos como el Old Fashioned o el Espresso Martini, sangría espumosa y sodas de la casa de lychee, maracuyá, sandía o frutos rojos.']},
    {'h3': 'Opciones vegetarianas', 'paras': [
      'La carta digital marca los platos vegetarianos y veganos y los alérgenos de cada plato. Entre ellos están la pizza Margarita, el bowl saludable vegano, los bowls del desayuno y las mazorcas laqueadas.']}
   ]},
  {'type': 'faq', 'items': [
    {'q': '¿Tengo que estar hospedado para comer en el 360° Rooftop?', 'a': 'No. El 360° Rooftop & Restaurante está abierto a huéspedes y visitantes.'},
    {'q': '¿Cómo reservo una mesa?', 'a': 'En línea, desde el botón Reservar mesa, o por WhatsApp al ' + WA + '.'},
    {'q': '¿Cuál es el horario del restaurante?', 'a': HOR_ES[0].upper() + HOR_ES[1:] + '.'},
    {'q': '¿El desayuno está incluido para los huéspedes?', 'a': 'Sí, el desayuno bufé está incluido en la tarifa de la habitación.'},
    {'q': '¿Hay opciones vegetarianas o veganas?', 'a': 'Sí. La carta digital marca los platos vegetarianos y veganos, y los alérgenos de cada plato.'},
    {'q': '¿Dónde queda el restaurante?', 'a': 'En el piso 11 de Haven, A Design Hotel, Calle 10A #36-29, El Poblado, a 2 minutos a pie de Provenza.'}]},
  {'type': 'links', 'title': 'Más planes <em>en el piso 11</em>', 'keys': ['rooftop', 'dia-de-sol', 'celebraciones', 'guia-restaurantes-provenza']},
 ],
 'en': [
  {'type': 'text', 'eyebrow': 'The 11th-floor menu', 'title': 'Local and international cooking, <em>morning to night</em>',
   'paras': [
    'Haven\'s kitchen is at the 360° Rooftop & Restaurant, on the 11th floor, with 360° views of Medellín. The menu blends local and international cooking, signature cocktails and a full bar, and it is open to guests and visitors.',
    'Opening hours: ' + HOR_EN + '. Book a table online or by WhatsApp at ' + WA + '.'],
   'sub': [
    {'h3': 'All-day breakfast', 'paras': [
      'Gourmet baked eggs with focaccia, an omelette with mozzarella, the Típico Medallo with beans as a tribute to paisa tradition, Greek yogurt or açaí bowls with fruit, and chicken or lentil soup. Hotel guests have the breakfast buffet included in their rate.']},
    {'h3': 'To share', 'paras': [
      'Tilapia ceviche, crab wontons, empanaditas, wings, Doble Crocante pork belly with patacones, glazed corn on the cob and a mixed grill for three. Pizzas come in 8 slices: Margherita, Pepperoni, Chicken and mushroom, and La Culpable, with chicken, pepperoni and pineapple.']},
    {'h3': 'Mains', 'paras': [
      '250-gram beef tenderloins with pepper sauce, flambéed or with ricotta and spinach; lomo saltado; 500-gram BBQ ribs; salmon en croûte or with ricotta and spinach; Galician-style octopus; seafood paella for two, seafood risotto and chaufa. For something casual, the Smash Burger or the burger trilogy.']},
    {'h3': 'Signature cocktails', 'paras': [
      'The house cocktails are the rooftop\'s signature: Watermelon Mezcal, Don Julio Garden, Havenly Haven, Catrina and Medallo, our take on the Moscow Mule. There are margaritas with Don Julio and Patrón, classics like the Old Fashioned and the Espresso Martini, sparkling sangria and house sodas in lychee, passion fruit, watermelon or red berries.']},
    {'h3': 'Vegetarian options', 'paras': [
      'The digital menu marks vegetarian and vegan dishes and the allergens in every dish. They include the Margherita pizza, the vegan healthy bowl, the breakfast bowls and the glazed corn.']}
   ]},
  {'type': 'faq', 'items': [
    {'q': 'Do I need to be a guest to eat at the 360° Rooftop?', 'a': 'No. The 360° Rooftop & Restaurant is open to guests and visitors.'},
    {'q': 'How do I book a table?', 'a': 'Online, with the Book a table button, or by WhatsApp at ' + WA + '.'},
    {'q': 'What are the restaurant\'s opening hours?', 'a': HOR_EN[0].upper() + HOR_EN[1:] + '.'},
    {'q': 'Is breakfast included for hotel guests?', 'a': 'Yes, the breakfast buffet is included in the room rate.'},
    {'q': 'Are there vegetarian or vegan options?', 'a': 'Yes. The digital menu marks vegetarian and vegan dishes, and the allergens in every dish.'},
    {'q': 'Where is the restaurant?', 'a': 'On the 11th floor of Haven, A Design Hotel, Calle 10A #36-29, El Poblado, a two-minute walk from Provenza.'}]},
  {'type': 'links', 'title': 'More plans <em>on the 11th floor</em>', 'keys': ['rooftop', 'dia-de-sol', 'celebraciones', 'guia-restaurantes-provenza']},
 ]}

# ---------------------------------------------------------------- SERVICIOS
M['servicios'] = {
 'es': [
  {'type': 'text', 'eyebrow': 'Cómo funciona', 'title': 'Un mensaje <em>y nos encargamos</em>',
   'paras': [
    'Todos los servicios se solicitan de la misma forma: escríbanos por WhatsApp al ' + WA + ' o pídalo en la recepción, que atiende las 24 horas. Algunos los presta Haven directamente y otros empresas aliadas certificadas; en ambos casos coordinamos por usted horarios, recogidas y confirmaciones.'],
   'sub': [
    {'h3': 'Incluido en su estadía', 'paras': [
      'Desayuno bufé, wifi gratuito en habitaciones y zonas comunes, limpieza diaria, recepción y seguridad las 24 horas, caja fuerte, artículos de aseo y acceso a la piscina climatizada y al bar del 360° Rooftop, en el piso 11, con vista de 360° de Medellín.']},
    {'h3': 'Llegar y moverse', 'paras': [
      'El traslado desde el Aeropuerto Internacional José María Córdova funciona las 24 horas, los 7 días de la semana, y se coordina con la recepción; el trayecto hasta El Poblado toma unos 40 minutos, según el tráfico. Si prefiere manejar, le ayudamos con el alquiler de un carro, y para conocer la región organizamos tours guiados por Antioquia.']},
    {'h3': 'Cuidarse durante el viaje', 'paras': [
      'Masajes, hidratación facial y terapia de sueros Ultra Vit en alianza con Pasitea Spa (vea Wellness), hidratación intravenosa con vitaminas y servicios dentales con atención profesional, a través de aliados certificados.']},
    {'h3': 'Comer sin salir', 'paras': [
      'La carta del 360° Rooftop & Restaurante, el menú de desayuno y la carta de Swing Lounge, el café del vestíbulo, abierto de 8:00 a. m. a 6:00 p. m. Cada habitación tiene minibar con bebidas y snacks.']}
   ]},
  {'type': 'faq', 'items': [
    {'q': '¿Cómo solicito un servicio?', 'a': 'Por WhatsApp al ' + WA + ' o en la recepción, que atiende las 24 horas.'},
    {'q': '¿Ofrecen traslado desde el aeropuerto?', 'a': 'Sí, las 24 horas, los 7 días de la semana. Se coordina con la recepción. El trayecto desde el aeropuerto José María Córdova hasta El Poblado toma unos 40 minutos, según el tráfico.'},
    {'q': '¿Qué incluye la tarifa de la habitación?', 'a': 'Desayuno bufé, wifi, limpieza diaria, recepción y seguridad 24 horas, caja fuerte, artículos de aseo y acceso a la piscina y al bar del 360° Rooftop.'},
    {'q': '¿Quién presta los servicios externos?', 'a': 'Empresas aliadas certificadas. Nosotros coordinamos la reserva y los horarios por usted.'},
    {'q': '¿Organizan tours por Antioquia?', 'a': 'Sí, coordinamos tours guiados por la región. Cuéntenos qué quiere conocer y en qué fechas.'}]},
  {'type': 'links', 'title': 'Más de <em>Haven</em>', 'keys': ['wellness', 'gastronomia', 'swing', 'guia-donde-hospedarse']},
 ],
 'en': [
  {'type': 'text', 'eyebrow': 'How it works', 'title': 'One message <em>and we take care of it</em>',
   'paras': [
    'Every service is requested the same way: message us on WhatsApp at ' + WA + ' or ask at the front desk, open 24 hours. Some services are provided by Haven and others by certified partner companies; either way, we coordinate times, pickups and confirmations for you.'],
   'sub': [
    {'h3': 'Included in your stay', 'paras': [
      'Breakfast buffet, free Wi-Fi in rooms and common areas, daily housekeeping, 24-hour front desk and security, a safe, toiletries and access to the heated pool and bar at the 360° Rooftop, on the 11th floor, with 360° views of Medellín.']},
    {'h3': 'Arriving and getting around', 'paras': [
      'Transfers from José María Córdova International Airport run 24 hours a day, 7 days a week, and are arranged through the front desk; the drive to El Poblado takes about 40 minutes, depending on traffic. If you prefer to drive, we help you rent a car, and to explore the region we arrange guided tours around Antioquia.']},
    {'h3': 'Looking after yourself', 'paras': [
      'Massages, facial hydration and Ultra Vit IV therapy in partnership with Pasitea Spa (see Wellness), IV hydration with vitamins and professional dental care, through certified partners.']},
    {'h3': 'Dining in', 'paras': [
      'The 360° Rooftop & Restaurant menu, the breakfast menu and the menu at Swing Lounge, the lobby café, open from 8:00 a.m. to 6:00 p.m. Every room has a minibar with drinks and snacks.']}
   ]},
  {'type': 'faq', 'items': [
    {'q': 'How do I request a service?', 'a': 'By WhatsApp at ' + WA + ' or at the front desk, open 24 hours.'},
    {'q': 'Do you offer airport transfers?', 'a': 'Yes, 24 hours a day, 7 days a week, arranged through the front desk. The drive from José María Córdova Airport to El Poblado takes about 40 minutes, depending on traffic.'},
    {'q': 'What is included in the room rate?', 'a': 'Breakfast buffet, Wi-Fi, daily housekeeping, 24-hour front desk and security, a safe, toiletries and access to the pool and bar at the 360° Rooftop.'},
    {'q': 'Who provides the external services?', 'a': 'Certified partner companies. We handle the booking and the schedule for you.'},
    {'q': 'Do you arrange tours around Antioquia?', 'a': 'Yes, we arrange guided tours around the region. Tell us what you would like to see and when.'}]},
  {'type': 'links', 'title': 'More from <em>Haven</em>', 'keys': ['wellness', 'gastronomia', 'swing', 'guia-donde-hospedarse']},
 ]}

# ---------------------------------------------------------------- ¿QUÉ SUITE VA CONTIGO?
M['que-suite-elegir'] = {
 'es': [
  {'type': 'text', 'eyebrow': 'Cómo elegir', 'title': 'Diecisiete espacios, <em>uno para cada viaje</em>',
   'paras': [
    'Haven, A Design Hotel tiene 37 habitaciones y suites en 17 tipos distintos, desde una habitación doble de 15 m² hasta la Suite Presidencial, un dúplex de 145 m². Doce tienen jacuzzi privado. Si prefiere decidir sin el quiz, esta guía resume cuál conviene según con quién viaja.'],
   'sub': [
    {'h3': 'En pareja, con jacuzzi', 'paras': [
      'La Viscount 803 tiene el jacuzzi afuera, en su balcón privado del piso 8, con vista a la ciudad. La Viscount suma balcón y una pequeña sala; la Ambassador y la Ambassador Jr, de 29 m², tienen jacuzzi, sala de estar y vista panorámica, y la Lord es la forma más directa de tener jacuzzi privado en 24 m².']},
    {'h3': 'Familia o amigos, de 4 a 6 personas', 'paras': [
      'Las Excelsior tienen 62 m², dos camas queen, jacuzzi y cocina: la 604 tiene dos jacuzzis, la 804 lo tiene en el balcón y la 401 suma terraza. La Imperial 402, de 68 m², tiene tres camas queen para seis personas, jacuzzi, balcón y cocineta.']},
    {'h3': 'Grupos de hasta 8 personas', 'paras': [
      'Las dos Emperor son penthouses para 8 personas con jacuzzi y cocina: la 901 tiene cuatro habitaciones en 138 m², y la 902, 111 m² con balcón. La Suite Presidencial es un dúplex de 145 m² con jacuzzi, zona de relajación, cocina y sala. Las tres tienen dos camas king y dos queen.']},
    {'h3': 'Viaje corto o de trabajo', 'paras': [
      'La Consort, de 15 m², es la opción compacta para dormir bien a 2 minutos de Provenza. La Duke y la Earl tienen 20 m²; la Duke suma una sala y la Earl, vista a la ciudad. La Marqués, de 24 m², y la Marqués Plus, de 27 m², tienen sala de estar para trabajar con calma.']}
   ]},
  {'type': 'rooms', 'title': 'Las más <em>elegidas</em>', 'intro': 'Todas incluyen desayuno bufé y acceso a la piscina climatizada del piso 11.', 'keys': ['room-habitacion-viscount-803', 'room-suite-excelsior-604', 'room-suite-emperor-901', 'room-suite-presidential', 'room-habitacion-ambassador-jr', 'room-habitacion-consort']},
  {'type': 'faq', 'items': [
    {'q': '¿Cuántas habitaciones tienen jacuzzi?', 'a': 'Doce de los 17 tipos de habitación tienen jacuzzi privado. La Viscount 803 y la Excelsior 804 lo tienen en el balcón, y la Excelsior 604 tiene dos.'},
    {'q': '¿Cuál es la suite más grande?', 'a': 'La Suite Presidencial, un dúplex de 145 m² para hasta 8 personas.'},
    {'q': '¿Qué opciones hay para 8 personas?', 'a': 'La Suite Emperor 901 (138 m²), la Emperor 902 (111 m², con balcón) y la Suite Presidencial (145 m²), todas con jacuzzi y cocina.'},
    {'q': '¿Qué habitaciones tienen cocina?', 'a': 'Las Excelsior 401, 604 y 804, las Emperor 901 y 902 y la Suite Presidencial tienen cocina; la Imperial 402 tiene cocineta.'},
    {'q': '¿Se puede alquilar el hotel completo?', 'a': 'No. Para grupos grandes recomendamos combinar suites como las Emperor o la Presidencial.'}]},
  {'type': 'links', 'title': 'Siga <em>explorando</em>', 'keys': ['suites-de-lujo', 'penthouse', 'escapada-romantica', 'cerca-parque-lleras']},
 ],
 'en': [
  {'type': 'text', 'eyebrow': 'How to choose', 'title': 'Seventeen spaces, <em>one for every trip</em>',
   'paras': [
    'Haven, A Design Hotel has 37 rooms and suites in 17 different types, from a 15 m² double room to the Presidential Suite, a 145 m² duplex. Twelve have a private jacuzzi. If you would rather decide without the quiz, this guide sums up which one suits you depending on who you travel with.'],
   'sub': [
    {'h3': 'As a couple, with a jacuzzi', 'paras': [
      'The Viscount 803 has its jacuzzi outdoors, on a private 8th-floor balcony with city views. The Viscount adds a balcony and a small lounge; the 29 m² Ambassador and Ambassador Jr have a jacuzzi, a lounge and panoramic views, and the Lord is the simplest way to get a private jacuzzi, in 24 m².']},
    {'h3': 'Family or friends, 4 to 6 people', 'paras': [
      'The Excelsior suites are 62 m², with two queen beds, a jacuzzi and a kitchen: the 604 has two jacuzzis, the 804 has it on the balcony and the 401 adds a terrace. The 68 m² Imperial 402 has three queen beds for six people, a jacuzzi, a balcony and a kitchenette.']},
    {'h3': 'Groups of up to 8', 'paras': [
      'Both Emperor suites are penthouses for 8 with a jacuzzi and a kitchen: the 901 has four bedrooms in 138 m², and the 902 is 111 m² with a balcony. The Presidential Suite is a 145 m² duplex with a jacuzzi, relaxation area, kitchen and lounge. All three have two king and two queen beds.']},
    {'h3': 'Short or business trips', 'paras': [
      'The 15 m² Consort is the compact choice for a good night\'s sleep two minutes from Provenza. The Duke and the Earl are 20 m²; the Duke adds a lounge and the Earl, city views. The 24 m² Marqués and the 27 m² Marqués Plus have a lounge area for working in peace.']}
   ]},
  {'type': 'rooms', 'title': 'Guest <em>favorites</em>', 'intro': 'All include the breakfast buffet and access to the heated pool on the 11th floor.', 'keys': ['room-habitacion-viscount-803', 'room-suite-excelsior-604', 'room-suite-emperor-901', 'room-suite-presidential', 'room-habitacion-ambassador-jr', 'room-habitacion-consort']},
  {'type': 'faq', 'items': [
    {'q': 'How many rooms have a jacuzzi?', 'a': 'Twelve of the 17 room types have a private jacuzzi. The Viscount 803 and the Excelsior 804 have it on the balcony, and the Excelsior 604 has two.'},
    {'q': 'Which is the largest suite?', 'a': 'The Presidential Suite, a 145 m² duplex for up to 8 people.'},
    {'q': 'What are the options for 8 people?', 'a': 'The Emperor 901 (138 m²), the Emperor 902 (111 m², with balcony) and the Presidential Suite (145 m²), all with a jacuzzi and a kitchen.'},
    {'q': 'Which rooms have a kitchen?', 'a': 'The Excelsior 401, 604 and 804, the Emperor 901 and 902 and the Presidential Suite have a kitchen; the Imperial 402 has a kitchenette.'},
    {'q': 'Can I rent the whole hotel?', 'a': 'No. For large groups we suggest combining suites such as the Emperor or the Presidential.'}]},
  {'type': 'links', 'title': 'Keep <em>exploring</em>', 'keys': ['suites-de-lujo', 'penthouse', 'escapada-romantica', 'cerca-parque-lleras']},
 ]}

out = os.path.join(os.path.dirname(__file__), 'more.json')
json.dump(M, open(out, 'w'), ensure_ascii=False, indent=1)
import re
for k, v in M.items():
    for L in ('es', 'en'):
        txt = json.dumps(v[L], ensure_ascii=False)
        words = len(re.sub(r'<[^>]+>', ' ', ' '.join(re.findall(r'"(?:paras|a|q|title|h3|intro)": (?:"([^"]*)"|\[([^\]]*)\])', txt) and [txt])).split())
        print(k, L, 'aprox palabras', len(re.findall(r'\w+', re.sub(r'"(type|keys|eyebrow|sub|items|paras|q|a|h3|title|intro)"', '', txt))))

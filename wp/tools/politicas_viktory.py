#!/usr/bin/env python3
"""Políticas de Haven unificadas con Viktory (5 nov 2026). Genera politicas-viktory.json con los cambios (texto viejo -> nuevo)
por idioma y página. Lo usan: este script (paquetes de importación) y politicas-viktory.php (base de datos en vivo)."""
import json, sys, os
H = os.path.dirname(os.path.abspath(__file__))
T, V = 'terminos-y-condiciones', 'politica-de-visitantes'
C = {'es': {T: [], V: []}, 'en': {T: [], V: []}}
def r(l, p, a, b): C[l][p].append([a, b])
# ---------- ES · Términos ----------
r('es', T, '<li>En nuestros establecimientos no se permite la salida tardía sin previo aviso y verificación de disponibilidad, por lo que los huéspedes que no cumplan con esta norma después de la hora establecida (11:00 AM) serán penalizados teniendo en cuenta las siguientes condiciones:</li><li>Desde las 12:00 hasta las 18:00 horas se cobrará el 50% de la tarifa diaria.</li><li>Después de las 18:00 horas se cobrará la noche completa.</li><li>Antes de las 15:00, se cobrará el 50% de la tarifa diaria.</li>',
  '<li>Check-in desde las 3:00 p. m. Check-out hasta las 11:00 a. m. Recepción 24 horas.</li><li>Early check-in: sujeto a disponibilidad. Antes de las 8:00 a. m. se cobra la noche completa. De 8:00 a. m. a 3:00 p. m. se cobra el 50 % de la tarifa diaria.</li><li>Late check-out sin aviso: de 12:00 m. a 6:00 p. m. se cobra el 50 % de la tarifa diaria. Después de las 6:00 p. m., la noche completa.</li>')
r('es', T, '<li>Si se encuentra en una habitación con jacuzzi, debe vigilar cuidadosamente el nivel del agua en todo momento. El desbordamiento del agua generará un cargo automático por limpieza de COP 100,000, además de cualquier cargo adicional por daños a la propiedad.</li>',
  '<li>Si su habitación tiene jacuzzi, debe vigilar el nivel del agua en todo momento. El desborde de agua genera una multa de USD 100, más los daños causados a la propiedad.</li>')
r('es', T, '<li>Está estrictamente prohibido hacer ruido o poner música a alto volumen después de las 22:00 horas. En caso de incumplimiento de esta norma, el huésped deberá abonar una multa de 100 USD. Cualquier alteración del orden público que requiera la intervención de la policía podrá conllevar la cancelación inmediata de la reserva sin derecho a reembolso por el resto de la estancia.</li>',
  '<li>Está prohibido hacer ruido o poner música a alto volumen después de las 10:00 p. m. Multa de USD 100. Si interviene la policía, la reserva puede cancelarse de inmediato sin reembolso por el resto de la estadía.</li>')
r('es', T, 'psicotrópicas en cualquier zona del hotel.</li></ul>',
  'psicotrópicas en cualquier zona del hotel.</li><li>Cada multa es de USD 100 y se carga a la cuenta del huésped. Los daños a la propiedad se cobran aparte, según su valor.</li></ul>')
r('es', T, 'Por lo tanto, HAVEN, A DESIGN HOTEL prohíbe fumar dentro de las instalaciones y solo se permitirá fumar en la zona de la piscina Rooftop 360°. En caso de que un huésped no cumpla con esta política, se le informará del recargo correspondiente por limpieza y eliminación de humo.',
  'Por lo tanto, HAVEN, A DESIGN HOTEL prohíbe fumar en todas sus instalaciones. Fumar dentro del hotel genera una multa de USD 100.')
r('es', T, 'artículo 17 de la Ley 679 de 2011', 'artículo 17 de la Ley 679 de 2001')
r('es', T, '<li>El número total de huéspedes no puede exceder la capacidad máxima de la habitación; estos no tendrán costo siempre y cuando los nombres de los huéspedes no cambien el mismo día del registro de entrada; de lo contrario, habrá un costo adicional de 120.000 por persona.</li>',
  '<li>El número total de huéspedes y visitantes no puede exceder la capacidad máxima de la habitación. Cada persona adicional sobre la capacidad tiene un cargo de COP 120.000 por día. Un huésped o visitante no registrado en la habitación genera una multa de USD 100 y la posible cancelación de la reserva sin reembolso.</li>')
# ---------- ES · Visitantes ----------
r('es', V, 'Según la ley colombiana, artículo 17 de la Ley 679 de 2011,', 'Según la ley colombiana, artículo 17 de la Ley 679 de 2001,')
r('es', V, '<p>Si el número total de huéspedes y visitantes excede el número máximo de la reserva, se aplicará un cargo de 120.000 COP por día calendario por persona adicional.</p>',
  '<p>Si el número total de huéspedes y visitantes excede el máximo de la reserva, se aplica un cargo de COP 120.000 por día calendario por cada persona adicional.</p>')
r('es', V, '<p>Cualquier huésped o visitante no registrado que ingrese a una habitación sin informar a la recepción, estaría infringiendo la ley colombiana y conllevaría un cargo automático de 300.000 COP y la posible cancelación de la reserva sin reembolso.</p>',
  '<p>Cualquier huésped o visitante no registrado que ingrese a una habitación sin informar a la recepción infringe la ley colombiana. Esto genera una multa automática de USD 100 y la posible cancelación de la reserva sin reembolso.</p>')
# ---------- EN · Terms ----------
r('en', T, '<li>Late check-out is not permitted in our establishments without prior notice and availability check-out. Guests who do not comply with this rule after the established time (11:00 AM) will be penalized according to the following conditions:</li><li>From 12:00 to 18:00, 50% of the daily rate will be charged.</li><li>After 6:00 PM, the full night’s rate will be charged.</li><li>Before 3:00 PM, 50% of the daily rate will be charged.</li>',
  '<li>Check-in from 3:00 pm. Check-out until 11:00 am. 24-hour reception.</li><li>Early check-in: subject to availability. Before 8:00 am, full night. From 8:00 am to 3:00 pm, 50% of the daily rate.</li><li>Late check-out without notice: from 12:00 pm to 6:00 pm, 50% of the daily rate. After 6:00 pm, full night.</li>')
r('en', T, '<li>If you are in a room with a jacuzzi, you must carefully monitor the water level at all times. Overflowing water will result in an automatic cleaning fee of COP 100,000, in addition to any further charges for property damage.</li>',
  '<li>If your room has a jacuzzi, you must watch the water level at all times. Water overflow: USD 100 fine, plus any damage caused to the property.</li>')
r('en', T, '<li>Making noise or playing loud music after 10:00 PM is strictly prohibited. Violating this rule will result in a $100 USD fine. Any disturbance of the peace requiring police intervention may lead to immediate cancellation of the reservation without refund for the remainder of the stay.</li>',
  '<li>Making noise or playing loud music is not allowed after 10:00 pm: USD 100 fine. If the police intervene, the reservation may be canceled immediately without refund for the rest of the stay.</li>')
r('en', T, 'psychotropic substances is prohibited in any area of the hotel..</li></ul>',
  'psychotropic substances is prohibited in any area of the hotel.</li><li>Each fine is USD 100 and is charged to the guest’s account. Damage to the property is charged separately, at its value.</li></ul>')
r('en', T, '<li>AUSENCIAS EN RESERVAS: La penalización por no presentarse será el importe total de la reserva.</li>',
  '<li>NO-SHOWS: The no-show penalty is the full amount of the reservation.</li>')
r('en', T, 'Therefore, HAVEN, A DESIGN HOTEL prohibits smoking within the premises, and smoking is only permitted in the Rooftop 360° pool area. Guests who do not comply with this policy will be informed of the corresponding surcharge for cleaning and smoke removal.',
  'Therefore, HAVEN, A DESIGN HOTEL does not allow smoking anywhere on the premises. Smoking inside the hotel results in a USD 100 fine.')
r('en', T, 'article 17 of Law 679 of 2011', 'article 17 of Law 679 of 2001')
r('en', T, '<li>The total number of guests cannot exceed the maximum capacity of the room; these will not be charged as long as the names of the guests do not change on the same day of check-in; otherwise, there will be an additional charge of 120,000 per person.</li>',
  '<li>The total number of guests and visitors cannot exceed the maximum capacity of the room. Extra person over capacity: COP 120,000 per day. Unregistered guest or visitor in the room: USD 100 fine and possible cancellation of the reservation without refund.</li>')
# ---------- EN · Visitors ----------
r('en', V, 'Article 17 of Law 679 of 2011,', 'Article 17 of Law 679 of 2001,')
r('en', V, '<p>If the total number of guests and visitors exceeds the maximum number of the reservation, a charge of COP 120,000 per calendar day per additional person will be applied.</p>',
  '<p>If the total number of guests and visitors exceeds the maximum of the reservation, a charge of COP 120,000 per calendar day applies for each additional person.</p>')
r('en', V, 'at no extra charge.Example B): If a primary guest books a 3-bedroom apartment that sleeps six, but is occupied by 3 guests, each guest may register one visitor per calendar day, up to a maximum of 3 visitors, at no extra charge.Example C): If a primary guest books a 3-bedroom apartment that sleeps six, but is occupied by 4 guests, each guest may register one visitor per calendar day, up to a maximum of 2 visitors, at no extra charge.</p>',
  'at no extra charge.</p>')
r('en', V, '<p>Any unregistered guest or visitor who enters a room without informing reception would be breaking Colombian law and would incur an automatic charge of 300,000 COP and the possible cancellation of the reservation without refund.</p>',
  '<p>Any unregistered guest or visitor who enters a room without informing reception is breaking Colombian law. This results in an automatic USD 100 fine and the possible cancellation of the reservation without refund.</p>')
json.dump(C, open(os.path.join(H, 'politicas-viktory.json'), 'w'), ensure_ascii=False, indent=1)
# Paquetes de importación del tema
if len(sys.argv) > 1:
    for l in C:
        f = os.path.join(sys.argv[1], 'import', 'bundle-%s.json' % l)
        b = json.load(open(f, encoding='utf-8'))
        for p, pairs in C[l].items():
            s = b['pages'][p]['prose']
            for a, z in pairs:
                n = s.count(a)
                print(l, p, 'OK' if n == 1 else ('YA' if z in s else 'NO ENCONTRADO x%d' % n), a[:60])
                if n == 1: s = s.replace(a, z)
            b['pages'][p]['prose'] = s
        json.dump(b, open(f, 'w', encoding='utf-8'), ensure_ascii=False, indent=2)

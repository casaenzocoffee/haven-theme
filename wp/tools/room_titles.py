#!/usr/bin/env python3
"""Títulos de las fichas por lo que busca el huésped (auditoría SEO 5 nov 2026). Genera room-titles.json y actualiza los paquetes."""
import json, os, sys
T = {
 'room-suite-emperor-901': ('Penthouse de 4 habitaciones con jacuzzi en Medellín · Emperor 901', '4-bedroom penthouse with jacuzzi in Medellín · Emperor 901'),
 'room-suite-emperor-902': ('Penthouse con jacuzzi y balcón para 8 en Medellín · Emperor 902', 'Penthouse with jacuzzi and balcony for 8 in Medellín · Emperor 902'),
 'room-suite-presidential': ('Suite Presidencial dúplex con jacuzzi en El Poblado | Haven', 'Duplex Presidential Suite with jacuzzi in El Poblado | Haven'),
 'room-suite-imperial-402': ('Suite de 3 habitaciones con jacuzzi y balcón · Imperial 402 | Haven', '3-bedroom suite with jacuzzi and balcony · Imperial 402 | Haven'),
 'room-suite-excelsior-401': ('Suite con jacuzzi, terraza y cocina en El Poblado · Excelsior 401', 'Suite with jacuzzi, terrace and kitchen in El Poblado · Excelsior 401'),
 'room-suite-excelsior-804': ('Suite de 2 habitaciones con jacuzzi y balcón · Excelsior 804 | Haven', '2-bedroom suite with jacuzzi and balcony · Excelsior 804 | Haven'),
 'room-suite-excelsior-604': ('Suite de 2 habitaciones con dos jacuzzis · Excelsior 604 | Haven', '2-bedroom suite with two jacuzzis · Excelsior 604 | Haven'),
 'room-habitacion-ambassador': ('Habitación con jacuzzi y sala en El Poblado · Ambassador | Haven', 'Room with jacuzzi and lounge in El Poblado · Ambassador | Haven'),
 'room-habitacion-ambassador-jr': ('Habitación con jacuzzi para parejas · Ambassador Jr | Haven Medellín', 'Jacuzzi room for couples · Ambassador Jr | Haven Medellín'),
 'room-habitacion-viscount-803': ('Habitación con jacuzzi en el balcón · Viscount 803 | Haven Medellín', 'Room with a jacuzzi on the balcony · Viscount 803 | Haven Medellín'),
 'room-habitacion-viscount': ('Habitación con jacuzzi y balcón en El Poblado · Viscount | Haven', 'Room with jacuzzi and balcony in El Poblado · Viscount | Haven'),
 'room-habitacion-lord': ('Habitación con jacuzzi en El Poblado, Medellín · Lord | Haven', 'Room with jacuzzi in El Poblado, Medellín · Lord | Haven'),
 'room-habitacion-earl': ('Habitación con vista a la ciudad en El Poblado · Earl | Haven', 'City-view room in El Poblado · Earl | Haven Medellín'),
 'room-habitacion-marques-plus': ('Habitación amplia con sala en El Poblado · Marqués Plus | Haven', 'Spacious room with lounge in El Poblado · Marques Plus | Haven'),
 'room-habitacion-marques': ('Habitación con sala cerca de Provenza · Marqués | Haven Medellín', 'Room with lounge near Provenza · Marques | Haven Medellín'),
 'room-habitacion-duke': ('Habitación doble con sala en El Poblado · Duke | Haven Medellín', 'Double room with lounge in El Poblado · Duke | Haven Medellín'),
 'room-habitacion-consort': ('Habitación doble compacta en El Poblado · Consort | Haven', 'Compact double room in El Poblado · Consort | Haven Medellín'),
}
H = os.path.dirname(os.path.abspath(__file__))
json.dump({k: {'es': v[0], 'en': v[1]} for k, v in T.items()}, open(os.path.join(H, 'room-titles.json'), 'w'), ensure_ascii=False, indent=1)
for k, (es, en) in T.items(): assert len(es) <= 72 and len(en) <= 72, (k, len(es), len(en))
if len(sys.argv) > 1:
    for i, l in enumerate(('es', 'en')):
        f = os.path.join(sys.argv[1], 'import', 'bundle-%s.json' % l)
        b = json.load(open(f, encoding='utf-8'))
        for k, v in T.items(): b['meta'].setdefault(k, {})['title'] = v[i]
        json.dump(b, open(f, 'w', encoding='utf-8'), ensure_ascii=False, indent=2)
    print('paquetes actualizados')

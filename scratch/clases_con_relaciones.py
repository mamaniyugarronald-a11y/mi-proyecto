from PIL import Image,ImageDraw,ImageFont
from pathlib import Path
out=Path(r'C:\laragon\www\proypebieai\docs\diagrama_clases_persistentes_pebie.png')
W,H=3200,2600
im=Image.new('RGB',(W,H),'#F4F6F9'); d=ImageDraw.Draw(im); F=Path(r'C:\Windows\Fonts')
title=ImageFont.truetype(str(F/'segoeuib.ttf'),36); head=ImageFont.truetype(str(F/'segoeuib.ttf'),18); body=ImageFont.truetype(str(F/'segoeui.ttf'),16); small=ImageFont.truetype(str(F/'segoeui.ttf'),14); relfont=ImageFont.truetype(str(F/'segoeuib.ttf'),15)
navy='#26384A'; cream='#F8EAD1'; headbg='#F4DDB7'; ink='#353535'; line='#414C57'; gold='#9E6C2B'; blue='#345E85'
d.text((45,25),'DIAGRAMA DE CLASES PERSISTENTES',font=title,fill=navy)
d.text((48,72),'Importadora PEBIE · Clases, relaciones verbales y multiplicidades',font=body,fill='#637386')
d.line((45,110,W-45,110),fill='#9BA9B7',width=2)
# Classes: nombre, x, y, ancho, atributos
C=[
('rol',55,180,450,[('PK','id_rol : int'),('','nombre_rol : varchar(100)')]),
('usuario',55,345,450,[('PK','id_usuario : int'),('','nombre : varchar(100)'),('','apellido : varchar(100)'),('UQ','usuario : varchar(50)'),('UQ','email : varchar(150)'),('','password : varchar(255)'),('FK','id_rol : int'),('','estado : boolean')]),
('historial_sesion',55,750,450,[('PK','id_historial : int'),('FK','id_usuario : int'),('','fecha_hora : datetime')]),
('cliente',55,930,450,[('PK','id_cliente : int'),('','tipo_registro : enum'),('','telefono : varchar(20)'),('','email : varchar(150)'),('','direccion : varchar(255)'),('','fecha_registro : datetime')]),
('cliente_unipersonal',55,1235,450,[('PK, FK','id_cliente : int'),('','nombre : varchar(100)'),('','apellido : varchar(100)'),('UQ','ci : varchar(50)')]),
('cliente_institucional',55,1450,450,[('PK, FK','id_cliente : int'),('','razon_social : varchar(150)'),('UQ','nit : varchar(50)'),('','contacto_nombre : varchar(150)')]),
('tipo_producto',825,180,450,[('PK','id_tipo_producto : int'),('','nombre_tipo : varchar(100)'),('','descripcion : varchar(255)')]),
('producto',825,345,450,[('PK','id_producto : int'),('','nombre : varchar(150)'),('','descripcion : text'),('','imagen : varchar(255)'),('','marca : varchar(100)'),('','precio_menor : decimal(10,2)'),('','precio_mayor : decimal(10,2)'),('','costo_compra : decimal(10,2)'),('','cantidad_min_mayor : int'),('','stock : int'),('','stock_minimo : int'),('FK','id_tipo_producto : int'),('FK','id_proveedor : int'),('','estado : tinyint')]),
('ubicacion',825,800,450,[('PK','id_ubicacion : int'),('','nombre_ubicacion : varchar(150)'),('','direccion : varchar(255)')]),
('detalle_ubicacion',825,985,450,[('PK','id_detalle_ubicacion : int'),('FK','id_ubicacion : int'),('FK','id_producto : int'),('','cantidad : int')]),
('proveedor',825,1190,450,[('PK','id_proveedor : int'),('','nombre_empresa : varchar(150)'),('','contacto : varchar(100)'),('','telefono : varchar(20)'),('','email : varchar(150)'),('','direccion : varchar(255)'),('','pais : varchar(100)'),('','calificacion : tinyint'),('','notas : text'),('','estado : tinyint')]),
('compra',1600,180,480,[('PK','id_compra : int'),('FK','id_proveedor : int'),('FK','id_usuario : int'),('','fecha : datetime'),('','nro_comprobante : varchar(50)')]),
('detalle_compra',1600,395,480,[('PK','id_detalle_compra : int'),('FK','id_compra : int'),('FK','id_producto : int'),('','cantidad : int'),('','precio_compra : decimal(10,2)')]),
('venta',1600,630,480,[('PK','id_venta : int'),('FK','id_cliente : int'),('FK','id_usuario : int'),('','fecha : datetime'),('','tipo_comprobante : enum'),('','tipo_cliente : enum'),('','es_venta_mayor : boolean'),('','promocion : enum'),('','descuento : decimal(10,2)'),('','metodo_pago : varchar(50)'),('','estado : varchar(30)'),('','estado_despacho : enum'),('','guia_despacho : varchar(100)'),('','fecha_despacho : datetime'),('','fecha_entrega : datetime'),('','tipo_venta : varchar(30)')]),
('detalle_venta',1600,1085,480,[('PK','id_detalle_venta : int'),('FK','id_venta : int'),('FK','id_producto : int'),('','cantidad : int'),('','precio_aplicado : decimal(10,2)')]),
('detalle_pedido',1600,1295,480,[('PK','id_detalle_pedido : int'),('FK','id_venta : int'),('FK','id_producto : int'),('','cantidad : int'),('','precio_estimado : decimal(10,2)')]),
('merma',2400,180,620,[('PK','id_merma : int'),('FK','id_producto : int'),('FK','id_usuario : int'),('','cantidad : int'),('','motivo : enum'),('','descripcion_dano : text'),('','fecha : datetime')]),
('reserva_producto',2400,480,620,[('PK','id_reserva : int'),('UQ','codigo_reserva : varchar(30)'),('IDX','grupo_reserva : varchar(40)'),('FK','id_producto : int'),('','nombre_cliente : varchar(150)'),('','telefono_cliente : varchar(50)'),('','cantidad : int'),('','precio_unitario : decimal(10,2)'),('','total_reserva : decimal(10,2)'),('','estado : enum'),('','fecha_limite : datetime'),('','fecha_creacion : datetime')]),
('configuracion_empresa',2400,880,620,[('PK','id_config : int'),('','razon_social : varchar(200)'),('','nit : varchar(50)'),('','direccion : varchar(255)'),('','telefono : varchar(50)'),('','email : varchar(150)'),('','ciudad : varchar(100)'),('','moneda_principal : varchar(20)'),('','logo_path : varchar(255)'),('','mensaje_pie_pagina : text')]),
('consulta_cliente',2400,1200,620,[('PK','id_consulta : int'),('','nombre_cliente : varchar(150)'),('','telefono : varchar(50)'),('','canal : enum'),('FK?','id_producto : int nullable'),('','mensaje_solicitud : text'),('','monto_cotizado : decimal(10,2)'),('','estado : enum'),('','fecha : datetime')]),
('seguimiento_importacion',2400,1510,620,[('PK','id_seguimiento : int'),('FK','id_importacion : int'),('','fecha_hora : datetime'),('','ubicacion : varchar(150)'),('','estado : varchar(50)'),('','detalle : text')]),
('cierre_caja',2400,1810,620,[('PK','id_cierre : int'),('FK','id_usuario : int'),('','fecha_cierre : date'),('','total_efectivo : decimal(12,2)'),('','total_transferencia_qr : decimal(12,2)'),('','total_general : decimal(12,2)'),('','total_ventas_conteo : int'),('','observaciones : text'),('','fecha_registro : datetime')]),
('importacion',1600,1510,620,[('PK','id_importacion : int'),('FK','id_proveedor : int'),('FK','id_usuario : int'),('','codigo_embarque : varchar(50)'),('','fecha_pedido : datetime'),('','estado_logistico : enum'),('','monto_fob_usd : decimal(12,2)'),('','costo_flete : decimal(10,2)'),('','costo_aduana_aranceles : decimal(10,2)'),('','costo_seguro : decimal(10,2)'),('','otros_gastos : decimal(10,2)'),('','total_gastos_internacion : decimal(12,2)'),('','tipo_cambio : decimal(6,2)'),('','pais_origen : varchar(100)'),('','fecha_llegada_estimada : date'),('','fecha_recibido : datetime'),('','observaciones : text')]),
('detalle_importacion',1600,1940,620,[('PK','id_detalle_importacion : int'),('FK','id_importacion : int'),('FK','id_producto : int'),('','cantidad : int'),('','precio_unitario_fob_usd : decimal(10,2)'),('','gasto_internacion_unitario_bs : decimal(10,2)'),('','costo_final_unitario_bs : decimal(10,2)')]),
]
boxes={n:(x,y,w,56+22*len(attrs)) for n,x,y,w,attrs in C}
def e(n,s):
 x,y,w,h=boxes[n]
 key=(n,s); idx=edge_uses.get(key,0); edge_uses[key]=idx+1
 offsets=[0,-34,34,-68,68,-102,102,-136,136]
 off=offsets[idx%len(offsets)]
 limit=max(0,(h if s in ('L','R') else w)//2-12); off=max(-limit,min(limit,off))
 if s=='L': return (x,y+h//2+off)
 if s=='R': return (x+w,y+h//2+off)
 if s=='T': return (x+w//2+off,y)
 return (x+w//2+off,y+h)

# (origen,destino,lados,verbo,cardinalidad origen/destino,ruta intermedia,posición etiqueta)
R=[
('rol','usuario','B','T','asigna','1','N',[],(280,318)),
('usuario','historial_sesion','B','T','registra sesión','1','N',[],(285,665)),
('cliente','cliente_unipersonal','B','T','se especializa en','1','0..1',[],(280,1214)),
('cliente','cliente_institucional','R','R','se especializa en','1','0..1',[(690,1030),(690,1520)],(690,1275)),
('tipo_producto','producto','B','T','clasifica','0..1','0..N',[],(1050,320)),
('proveedor','producto','R','R','provee','0..1','0..N',[(1460,1320),(1460,520)],(1460,910)),
('proveedor','compra','R','L','abastece','1','N',[(1460,1330),(1460,260)],(1460,790)),
('usuario','compra','R','T','registra','1','N',[(700,460),(700,135),(1840,135)],(1250,135)),
('compra','detalle_compra','B','T','se detalla en','1','N',[(2110,340),(2110,470)],(2110,405)),
('producto','detalle_compra','R','L','se incluye en','1','N',[],(1435,490)),
('ubicacion','detalle_ubicacion','B','T','organiza','1','N',[(1460,940),(1460,1050)],(1460,990)),
('producto','detalle_ubicacion','R','R','se distribuye en','1','N',[(1460,540),(1460,1050)],(1460,800)),
('cliente','venta','R','L','realiza','1','N',[(700,1030),(700,1680),(1560,1680),(1560,870)],(1115,1680)),
('usuario','venta','R','L','registra','1','N',[(700,460),(700,1715),(1560,1715),(1560,870)],(1120,1715)),
('venta','detalle_venta','R','R','contiene','1','N',[(2110,870),(2110,1170)],(2110,1010)),
('producto','detalle_venta','R','L','se vende en','1','N',[],(1435,1170)),
('venta','detalle_pedido','R','R','incluye','1','N',[(2140,870),(2140,1380)],(2140,1210)),
('producto','detalle_pedido','R','L','se solicita en','1','N',[],(1435,1380)),
('producto','merma','R','L','origina','1','N',[(1460,520),(1460,135),(2360,135),(2360,280)],(2040,135)),
('usuario','merma','R','T','registra','1','N',[(700,460),(700,155),(2710,155)],(2050,155)),
('producto','reserva_producto','R','L','se reserva en','1','N',[(1460,520),(1460,1750),(2360,1750),(2360,640)],(2050,1750)),
('proveedor','importacion','R','L','origina','1','N',[(1460,1380),(1460,1690)],(1460,1540)),
('usuario','importacion','R','T','gestiona','1','N',[(710,460),(710,1470),(1900,1470)],(1300,1470)),
('importacion','detalle_importacion','B','T','se detalla en','1','N',[(2330,1900),(2330,2010)],(2330,1950)),
('producto','detalle_importacion','R','L','se importa en','1','N',[(1460,520),(1460,2160)],(1460,2100)),
('importacion','seguimiento_importacion','R','L','registra hitos','1','N',[(2340,1650),(2340,1740)],(2340,1690)),
('producto','consulta_cliente','R','L','se consulta','0..1','0..N',[(1460,520),(1460,2290),(2360,2290),(2360,1390)],(2020,2290)),
('usuario','cierre_caja','R','L','registra cierre','1','N',[(710,460),(710,2390),(2360,2390),(2360,2020)],(1510,2390)),
]
edge_uses={}

# Connector labels are placed directly on their association lines.
for a,b,sa,sb,verb,ma,mb,mid,lp in R:
 p1,p2=e(a,sa),e(b,sb); path=[p1]+mid+[p2]
 d.line(path,fill=line,width=3,joint='curve')
 for px,py in (p1,p2): d.ellipse((px-5,py-5,px+5,py+5),fill=line)
 d.text((p1[0]+7,p1[1]+4),ma,font=small,fill='#273746',stroke_width=3,stroke_fill='#F4F6F9')
 d.text((p2[0]+7,p2[1]+4),mb,font=small,fill='#273746',stroke_width=3,stroke_fill='#F4F6F9')
 bb=d.textbbox((0,0),verb,font=relfont); tw=bb[2]-bb[0]; th=bb[3]-bb[1]; lx,ly=lp
 d.rounded_rectangle((lx-tw//2-8,ly-th//2-5,lx+tw//2+8,ly+th//2+5),radius=5,fill='#FFFFFF',outline='#8795A3',width=1)
 d.text((lx-tw//2,ly-th//2-1),verb,font=relfont,fill=navy)

# EA-like class tables: class name and persistent attributes.
for n,x,y,w,attrs in C:
 h=boxes[n][3]
 d.rectangle((x+5,y+5,x+w+5,y+h+5),fill='#D0D3D6')
 d.rectangle((x,y,x+w,y+h),fill=cream,outline='#60513F',width=2)
 d.rectangle((x+1,y+1,x+w-1,y+49),fill=headbg,outline='#60513F',width=1)
 # Las tablas puente representan asociaciones N:M y llevan esa información
 # dentro de la cabecera, integrada en la clase persistente.
 bridge_titles={
  'detalle_compra':'detalle_compra  «N:M compra-producto»',
  'detalle_venta':'detalle_venta  «N:M venta-producto»',
  'detalle_pedido':'detalle_pedido  «N:M pedido-producto»',
  'detalle_ubicacion':'detalle_ubicacion  «N:M ubicación-producto»',
 }
 d.text((x+12,y+14),bridge_titles.get(n,n),font=head,fill='#302A22')
 d.line((x,y+50,x+w,y+50),fill='#60513F',width=1)
 for i,(tag,val) in enumerate(attrs):
  yy=y+55+i*22; col=gold if 'PK' in tag else (blue if 'FK' in tag else ink)
  d.text((x+11,yy),'-',font=body,fill=col); d.text((x+34,yy),val,font=body,fill=ink)
  if tag: d.text((x+w-58,yy),tag,font=small,fill=col)
d.text((55,2540),'Multiplicidades en los extremos: 1 = uno · 0..N = cero o muchos · 0..1 = opcional. FK indica campos de referencia según migraciones; estas no declaran constraints FOREIGN KEY.',font=small,fill='#566D7C')
im.save(out,dpi=(180,180),optimize=True); print(out)

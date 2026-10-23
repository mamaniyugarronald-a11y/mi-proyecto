from PIL import Image, ImageDraw
from pathlib import Path
p=Path(r'C:\laragon\www\proypebieai\public\img\productos')
files=sorted(p.glob('*'),key=lambda f:(f.suffix,f.name))
thumb=Image.new('RGB',(900,((len(files)+4)//5)*190),'white')
d=ImageDraw.Draw(thumb)
for i,f in enumerate(files):
 im=Image.open(f).convert('RGB')
 im.thumbnail((160,150))
 x=(i%5)*180+(180-im.width)//2
 y=(i//5)*190
 thumb.paste(im,(x,y))
 d.text(((i%5)*180+5,y+153),f.name,fill='black')
thumb.save(r'C:\laragon\www\proypebieai\scratch\product_images_contact.png')
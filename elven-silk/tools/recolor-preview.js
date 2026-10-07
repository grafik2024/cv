const sharp=require('sharp'), fs=require('fs');
const SRC='../assets/';
const OUT='../assets/cfg/';
fs.mkdirSync(OUT,{recursive:true});

const SCENES = [
  {id:"czarny", src:"czerwony-czarne-opakowanie"},   // czarne opakowanie
  {id:"korona",    src:"czerwony-tiara"},            // z koroną
  {id:"maskotka",  src:"niebieski-z-maskotka"},      // z maskotką
  {id:"led",       src:"swiecacy-led"},              // podświetlenie
  {id:"brokat",    src:"rozowy-brokat-motyl"},       // brokat + motyle
  {id:"slodycze",  src:"walentynkowy-pralinki"}      // balon + pralinki
];
const COLORS = [
  {id:"czerwony", hex:"#C8102E"}, {id:"bordo", hex:"#7B1230"}, {id:"roz", hex:"#F2789F"},
  {id:"pudrowy",  hex:"#EFC2C5"}, {id:"kremowy", hex:"#F1E3CE"}, {id:"bialy", hex:"#F7F5F3"},
  {id:"blekit",   hex:"#7FC6E8"}, {id:"granat", hex:"#1F3F91"}, {id:"fiolet", hex:"#7A4A9E"},
  {id:"czarny",   hex:"#2A2429"}, {id:"szampan", hex:"#D9BE86"}
];

function rgb2hsl(r,g,b){ r/=255;g/=255;b/=255;
  const mx=Math.max(r,g,b), mn=Math.min(r,g,b), l=(mx+mn)/2; let h=0,s=0;
  if(mx!==mn){ const d=mx-mn; s = l>0.5 ? d/(2-mx-mn) : d/(mx+mn);
    if(mx===r) h=((g-b)/d + (g<b?6:0)); else if(mx===g) h=(b-r)/d+2; else h=(r-g)/d+4; h/=6; }
  return [h,s,l];
}
function hue2rgb(p,q,t){ if(t<0)t+=1; if(t>1)t-=1;
  if(t<1/6)return p+(q-p)*6*t; if(t<1/2)return q; if(t<2/3)return p+(q-p)*(2/3-t)*6; return p; }
function hsl2rgb(h,s,l){ let r,g,b;
  if(s===0){ r=g=b=l; } else { const q=l<0.5?l*(1+s):l+s-l*s, p=2*l-q;
    r=hue2rgb(p,q,h+1/3); g=hue2rgb(p,q,h); b=hue2rgb(p,q,h-1/3); }
  return [r*255,g*255,b*255];
}
const hueDist=(a,b)=>{ let d=Math.abs(a-b); return d>0.5 ? 1-d : d; };
const clamp=(v,a,b)=>v<a?a:v>b?b:v;

(async()=>{
 const manifest={};
 for(const sc of SCENES){
   const file = SRC+sc.src+'-1000.webp';
   const {data,info} = await sharp(file).ensureAlpha().raw().toBuffer({resolveWithObject:true});
   const px = info.width*info.height, ch = info.channels;
   // measure the dominant saturated hue of the source bouquet
   let sx=0, sy=0, ss=0, sl=0, n=0;
   for(let i=0;i<px;i++){
     const [h,s,l]=rgb2hsl(data[i*ch],data[i*ch+1],data[i*ch+2]);
     if(s>0.28 && l>0.12 && l<0.9){ sx+=Math.cos(h*2*Math.PI); sy+=Math.sin(h*2*Math.PI); ss+=s; sl+=l; n++; }
   }
   let baseH=Math.atan2(sy/n, sx/n)/(2*Math.PI); if(baseH<0)baseH+=1;
   const baseS=ss/n, baseL=sl/n;
   console.log(sc.id, 'baseHue', (baseH*360).toFixed(0)+'°', 'S', baseS.toFixed(2), 'L', baseL.toFixed(2), 'px', n);

   for(const col of COLORS){
     const tr=parseInt(col.hex.slice(1,3),16), tg=parseInt(col.hex.slice(3,5),16), tb=parseInt(col.hex.slice(5,7),16);
     const [tH,tS,tL]=rgb2hsl(tr,tg,tb);
     const out=Buffer.alloc(px*3);
     for(let i=0;i<px;i++){
       const r=data[i*ch], g=data[i*ch+1], b=data[i*ch+2];
       const [h,s,l]=rgb2hsl(r,g,b);
       const w = clamp((s-0.12)/0.16,0,1) * clamp((0.155-hueDist(h,baseH))/0.05,0,1);
       if(w<=0.001){ out[i*3]=r; out[i*3+1]=g; out[i*3+2]=b; continue; }
       const nS = clamp(tS*(0.55+0.45*(s/baseS)),0,1);
       const nL = clamp(tL+(l-baseL)*0.9,0.02,0.985);
       const [nr,ng,nb]=hsl2rgb(tH,nS,nL);
       out[i*3]   = clamp(r+(nr-r)*w,0,255);
       out[i*3+1] = clamp(g+(ng-g)*w,0,255);
       out[i*3+2] = clamp(b+(nb-b)*w,0,255);
     }
     await sharp(out,{raw:{width:info.width,height:info.height,channels:3}})
       .resize({width:760}).webp({quality:78,effort:5}).toFile(OUT+sc.id+'-'+col.id+'.webp');
   }
   manifest[sc.id]={src:sc.src, baseHue:Math.round(baseH*360)};
 }
 fs.writeFileSync(OUT+'_scenes.json', JSON.stringify(manifest,null,1));
 const files=fs.readdirSync(OUT).filter(f=>f.endsWith('.webp'));
 let t=0; files.forEach(f=>t+=fs.statSync(OUT+f).size);
 console.log(files.length,'files,',(t/1048576).toFixed(2),'MB');
})();

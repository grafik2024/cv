const sharp=require('sharp');
const OUT='../assets/';
(async()=>{
  const {data,info}=await sharp('logo-src.jpg').greyscale().normalise().raw().toBuffer({resolveWithObject:true});
  const rgba=Buffer.alloc(info.width*info.height*4);
  for(let i=0;i<info.width*info.height;i++){
    let a=255-data[i];
    a = a<40 ? 0 : Math.min(255, Math.round((a-40)*(255/175)));
    rgba[i*4]=0; rgba[i*4+1]=0; rgba[i*4+2]=0; rgba[i*4+3]=a;
  }
  const flat = await sharp(rgba,{raw:{width:info.width,height:info.height,channels:4}}).png().toBuffer();
  await sharp(flat).trim({threshold:1}).resize({width:640,fit:'inside'}).png({compressionLevel:9}).toFile(OUT+'logo-lockup.png');
  const cropped = await sharp(flat).extract({left:0,top:0,width:info.width,height:Math.round(info.height*0.80)}).png().toBuffer();
  await sharp(cropped).trim({threshold:1}).resize({width:256,fit:'inside'}).png({compressionLevel:9}).toFile(OUT+'logo-mark.png');
  const a=await sharp(OUT+'logo-lockup.png').metadata(), b=await sharp(OUT+'logo-mark.png').metadata();
  console.log('lockup',a.width+'x'+a.height,(a.size/1024).toFixed(1)+'kB','| mark',b.width+'x'+b.height);
  await sharp({create:{width:1040,height:340,channels:4,background:'#120C11'}}).composite([
    {input:await sharp(OUT+'logo-mark.png').resize({width:150}).negate({alpha:false}).toBuffer(),left:60,top:95},
    {input:await sharp(OUT+'logo-lockup.png').resize({width:300}).negate({alpha:false}).toBuffer(),left:270,top:110},
    {input:await sharp({create:{width:400,height:340,channels:4,background:'#FBF7F4'}}).png().toBuffer(),left:640,top:0},
    {input:await sharp(OUT+'logo-lockup.png').resize({width:300}).toBuffer(),left:690,top:110}
  ]).png().toFile('logo-check.png');
  console.log('ok');
})();

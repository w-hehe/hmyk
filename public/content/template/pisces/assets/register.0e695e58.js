import{F as x,H as w,r as C}from"./footer.dd270afd.js";import{_ as V,d as y,r as _,a as B,o as k,w as E,c as A,b as e,f as t,u as F,h as n,i as R,j as U,n as c,p as H}from"./index.08eb56a6.js";const N={class:"container"},T=c("\u7ACB\u5373\u6CE8\u518C"),$=c("\u767B\u5F55"),j=y({__name:"register",setup(I){const s=_({data:{site_name:"",template:{site:{}}},loading:!0}),a=_({account:"",password:"",repassword:""}),f=()=>{C(a).then(u=>{console.log(u),u.code==1&&d.push({path:"/user/login"})})},d=F(),i=B();return k(()=>{E(()=>{s.data.template=i.value.state.data,H("\u6CE8\u518C - "+s.data.template.site.shop_title),s.data.template.site.register==0&&d.push({path:"/"})})}),(u,o)=>{const p=n("el-input"),r=n("el-form-item"),m=n("el-button"),b=n("el-form"),g=n("el-card"),v=n("el-main"),h=n("el-container");return R(),A("div",N,[e(w,{default_active:"2",ref_key:"headerRef",ref:i},null,512),e(h,null,{default:t(()=>[e(v,{style:{"max-width":"520px",margin:"0 auto",padding:"0 10px","margin-top":"15px"}},{default:t(()=>[e(g,{class:"box-card",style:{"padding-top":"30px 20px"}},{default:t(()=>[e(b,{model:a,"label-width":"120px","label-position":"top"},{default:t(()=>[e(r,{label:"\u90AE\u7BB1"},{default:t(()=>[e(p,{modelValue:a.account,"onUpdate:modelValue":o[0]||(o[0]=l=>a.account=l),type:"email"},null,8,["modelValue"])]),_:1}),e(r,{label:"\u5BC6\u7801"},{default:t(()=>[e(p,{modelValue:a.password,"onUpdate:modelValue":o[1]||(o[1]=l=>a.password=l),type:"password"},null,8,["modelValue"])]),_:1}),e(r,{label:"\u786E\u8BA4\u5BC6\u7801"},{default:t(()=>[e(p,{modelValue:a.repassword,"onUpdate:modelValue":o[2]||(o[2]=l=>a.repassword=l),type:"password"},null,8,["modelValue"])]),_:1}),e(r,null,{default:t(()=>[e(m,{type:"primary",onClick:f},{default:t(()=>[T]),_:1}),e(m,{onClick:o[3]||(o[3]=l=>U(d).push({path:"/user/login"}))},{default:t(()=>[$]),_:1})]),_:1})]),_:1},8,["model"])]),_:1})]),_:1})]),_:1}),e(x,{pet_name:s.data.template.site.shop_pet_name,beian:s.data.template.site.beian},null,8,["pet_name","beian"])])}}});var q=V(j,[["__scopeId","data-v-552865ce"]]);export{q as default};
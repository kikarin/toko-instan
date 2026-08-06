import { useState } from 'react'

// ─── Design tokens — soft light ─────────────────────────────────────────────
const T = {
  bg:        '#f5f4f0',
  surface:   '#faf9f6',
  panel:     '#ffffff',
  border:    'rgba(0,0,0,0.07)',
  borderHi:  'rgba(0,0,0,0.13)',
  text:      '#1c1c22',
  sub:       '#4a4a57',
  muted:     '#9090a0',
  dim:       '#c8c8d5',
  // accents
  amber:     '#e07c28',
  amberSoft: 'rgba(224,124,40,0.10)',
  amberGlow: 'rgba(224,124,40,0.18)',
  violet:    '#6d4fc2',
  violetSoft:'rgba(109,79,194,0.10)',
  teal:      '#0e9f8a',
  tealSoft:  'rgba(14,159,138,0.10)',
  rose:      '#e0405a',
  roseSoft:  'rgba(224,64,90,0.10)',
  sky:       '#3b82f6',
  skySoft:   'rgba(59,130,246,0.10)',
  green:     '#22a15a',
  greenSoft: 'rgba(34,161,90,0.10)',
  // sidebar
  sidebar:   '#1e1c2a',
  sidebarBorder: 'rgba(255,255,255,0.06)',
}

const fmt = (n: number) =>
  n >= 1_000_000_000 ? `${(n/1_000_000_000).toFixed(1)}M`
  : n >= 1_000_000   ? `${(n/1_000_000).toFixed(1)}Jt`
  : n >= 1_000       ? `${(n/1_000).toFixed(0)}rb`
  : `${n}`
const fmtRp = (n: number) => `Rp ${fmt(n)}`

// ─── Data ────────────────────────────────────────────────────────────────────
type Period = 'Hari' | 'Minggu' | 'Bulan'
const periods: Period[] = ['Hari', 'Minggu', 'Bulan']

function genRev(n: number, base: number, spread: number) {
  return Array.from({ length: n }, (_, i) => ({
    i, v: Math.round(base + (Math.sin(i*0.9)*0.4 + Math.random()*0.6) * spread),
  }))
}
const revSeries: Record<Period, {i:number;v:number}[]> = {
  Hari:   genRev(30, 1_800_000, 2_400_000),
  Minggu: genRev(12, 11_000_000, 14_000_000),
  Bulan:  genRev(12, 44_000_000, 28_000_000),
}
const revLabels: Record<Period, string[]> = {
  Hari:   Array.from({length:30},(_,i)=>`${i+1}`),
  Minggu: ['W1','W2','W3','W4','W5','W6','W7','W8','W9','W10','W11','W12'],
  Bulan:  ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
}

const kpis = [
  { label:'Gross Revenue',   value:'Rp 214Jt',  delta:'+18.4%', up:true,  sub:'vs bulan lalu', c:T.amber,  cs:T.amberSoft  },
  { label:'Total Pesanan',   value:'8.341',      delta:'+11.2%', up:true,  sub:'order masuk',   c:T.teal,   cs:T.tealSoft   },
  { label:'Unique Visitor',  value:'54.921',     delta:'+26.7%', up:true,  sub:'sesi unik',     c:T.sky,    cs:T.skySoft    },
  { label:'Konversi',        value:'4.12%',      delta:'-0.3%',  up:false, sub:'dari pengunjung',c:T.rose,  cs:T.roseSoft   },
  { label:'Avg. Order',      value:'Rp 256rb',   delta:'+6.8%',  up:true,  sub:'per transaksi', c:T.violet, cs:T.violetSoft },
  { label:'Saldo Dompet',    value:'Rp 38.4Jt',  delta:'live',   up:null,  sub:'siap tarik',    c:T.green,  cs:T.greenSoft  },
]

const orderFlow = [
  { label:'Pending',    n:124,  c:'#d97706' },
  { label:'Diproses',   n:312,  c:T.sky     },
  { label:'Dikemas',    n:198,  c:T.violet  },
  { label:'Dikirim',    n:541,  c:T.teal    },
  { label:'Selesai',    n:6821, c:T.green   },
  { label:'Dibatalkan', n:148,  c:T.rose    },
]

const topSellers = [
  { name:'NovaBatik Studio',  gmv:'Rp 18.4Jt', orders:412, rating:4.9, badge:'top', avatar:'NB', hue:220 },
  { name:'KuliKain Official', gmv:'Rp 14.1Jt', orders:318, rating:4.8, badge:'pro', avatar:'KK', hue:280 },
  { name:'Jaya Elektronik',   gmv:'Rp 11.7Jt', orders:287, rating:4.7, badge:null,  avatar:'JE', hue:190 },
  { name:'Warung Digital ID', gmv:'Rp 9.2Jt',  orders:234, rating:4.6, badge:null,  avatar:'WD', hue:150 },
  { name:'Mode Nusantara',    gmv:'Rp 7.8Jt',  orders:198, rating:4.5, badge:null,  avatar:'MN', hue:30  },
]

const mktProducts = [
  { id:1,  name:'Kemeja Batik Tenun Premium',   price:'Rp 285.000', sold:1240, rating:4.9, store:'NovaBatik Studio',  img:'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=400&h=400&fit=crop&auto=format', tag:'Bestseller', cat:'Fashion'    },
  { id:2,  name:'Sneakers Casual Kulit Asli',   price:'Rp 599.000', sold:847,  rating:4.8, store:'Mode Nusantara',    img:'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&auto=format', tag:'Baru',       cat:'Sepatu'    },
  { id:3,  name:'Mechanical Keyboard TKL 75%',  price:'Rp 890.000', sold:632,  rating:4.7, store:'Jaya Elektronik',   img:'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=400&h=400&fit=crop&auto=format', tag:'Hot',        cat:'Elektronik'},
  { id:4,  name:'Tas Kulit Selempang Minimalis',price:'Rp 420.000', sold:921,  rating:4.8, store:'KuliKain Official', img:'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400&h=400&fit=crop&auto=format', tag:'Bestseller', cat:'Aksesoris' },
  { id:5,  name:'Matcha Latte Premium 200gr',   price:'Rp 145.000', sold:2103, rating:4.9, store:'Warung Digital ID', img:'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400&h=400&fit=crop&auto=format', tag:'Hot',        cat:'Kuliner'   },
  { id:6,  name:'Kacamata Frame Titanium',      price:'Rp 760.000', sold:438,  rating:4.7, store:'Mode Nusantara',    img:'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=400&h=400&fit=crop&auto=format', tag:'Baru',       cat:'Aksesoris' },
  { id:7,  name:'Headphone Over-ear Wireless',  price:'Rp 1.250.000',sold:512, rating:4.8, store:'Jaya Elektronik',   img:'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=400&fit=crop&auto=format', tag:null,         cat:'Elektronik'},
  { id:8,  name:'Celana Linen Wide Leg',        price:'Rp 320.000', sold:763,  rating:4.6, store:'NovaBatik Studio',  img:'https://images.unsplash.com/photo-1594938298603-c8148c4b4b58?w=400&h=400&fit=crop&auto=format', tag:null,         cat:'Fashion'   },
]
const mktCats = ['Semua','Fashion','Elektronik','Sepatu','Aksesoris','Kuliner']

const navItems = [
  { icon:'◈', label:'Dashboard'   },
  { icon:'⊡', label:'Marketplace' },
  { icon:'⬡', label:'Produk'      },
  { icon:'◉', label:'Pesanan'     },
  { icon:'⊕', label:'Pelanggan'   },
  { icon:'◎', label:'Dompet'      },
  { icon:'◇', label:'Voucher'     },
  { icon:'⊞', label:'Analitik'    },
]

// ─── Sparkline ───────────────────────────────────────────────────────────────
function Spark({ data, color }: { data: number[]; color: string }) {
  const w = 72, h = 26
  const min = Math.min(...data), max = Math.max(...data)
  const x = (i: number) => (i / (data.length - 1)) * w
  const y = (v: number) => h - ((v - min) / (max - min || 1)) * (h - 4) - 2
  const pts = data.map((v,i) => `${x(i)},${y(v)}`).join(' ')
  const area = `M${x(0)},${y(data[0])} ` + data.slice(1).map((v,i)=>`L${x(i+1)},${y(v)}`).join(' ') + ` L${x(data.length-1)},${h} L0,${h} Z`
  const id = `sg${color.replace('#','')}`
  return (
    <svg viewBox={`0 0 ${w} ${h}`} style={{width:w,height:h,display:'block'}}>
      <defs>
        <linearGradient id={id} x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stopColor={color} stopOpacity="0.25"/>
          <stop offset="100%" stopColor={color} stopOpacity="0"/>
        </linearGradient>
      </defs>
      <path d={area} fill={`url(#${id})`}/>
      <polyline points={pts} fill="none" stroke={color} strokeWidth="1.5" strokeLinejoin="round" strokeLinecap="round"/>
    </svg>
  )
}

// ─── Revenue area chart ───────────────────────────────────────────────────────
function RevenueChart({ data, labels }: { data:{i:number;v:number}[]; labels:string[] }) {
  const W=800, H=200, pl=4, pr=4, pt=16, pb=28
  const vals = data.map(d=>d.v)
  const max = Math.max(...vals)*1.08, min = Math.min(...vals)*0.92
  const x = (i:number) => pl + (i/(data.length-1))*(W-pl-pr)
  const y = (v:number) => pt + ((max-v)/(max-min))*(H-pt-pb)
  const pts = data.map(d=>`${x(d.i)},${y(d.v)}`).join(' ')
  const area = `M${x(0)},${y(data[0].v)} `
    + data.slice(1).map(d=>`L${x(d.i)},${y(d.v)}`).join(' ')
    + ` L${x(data.length-1)},${H-pb} L${x(0)},${H-pb} Z`
  const [hov, setHov] = useState<number|null>(null)
  const step = Math.ceil(data.length/7)

  return (
    <svg viewBox={`0 0 ${W} ${H}`} className="w-full" style={{fontFamily:"'DM Mono',monospace"}}>
      <defs>
        <linearGradient id="ra" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stopColor={T.amber} stopOpacity="0.15"/>
          <stop offset="100%" stopColor={T.amber} stopOpacity="0"/>
        </linearGradient>
        <linearGradient id="rl" x1="0" y1="0" x2="1" y2="0">
          <stop offset="0%" stopColor="#f59e0b"/>
          <stop offset="100%" stopColor={T.amber}/>
        </linearGradient>
      </defs>

      {/* grid lines */}
      {[0.25,0.5,0.75].map(f => {
        const v = min+(max-min)*f
        return <line key={f} x1={pl} x2={W-pr} y1={y(v)} y2={y(v)} stroke={T.border} strokeWidth="1"/>
      })}

      <path d={area} fill="url(#ra)"/>
      <polyline points={pts} fill="none" stroke="url(#rl)" strokeWidth="2" strokeLinejoin="round" strokeLinecap="round"/>

      {data.map((d,i) => (
        <g key={i} onMouseEnter={()=>setHov(i)} onMouseLeave={()=>setHov(null)} style={{cursor:'crosshair'}}>
          <rect x={x(i)-12} y={pt} width={24} height={H-pt-pb} fill="transparent"/>
          {hov===i && <>
            <line x1={x(i)} x2={x(i)} y1={pt} y2={H-pb} stroke={T.amber} strokeWidth="1" strokeDasharray="3 3" opacity="0.5"/>
            <circle cx={x(i)} cy={y(d.v)} r="4" fill={T.amber} stroke={T.panel} strokeWidth="2"/>
            <rect x={Math.min(x(i)-44,W-96)} y={y(d.v)-40} width={88} height={30} rx="8"
              fill={T.panel} stroke={T.border} strokeWidth="1"
              style={{filter:'drop-shadow(0 2px 8px rgba(0,0,0,0.08))'}}/>
            <text x={Math.min(x(i)-44,W-96)+44} y={y(d.v)-23} textAnchor="middle" fontSize="9" fill={T.muted}>{labels[i]}</text>
            <text x={Math.min(x(i)-44,W-96)+44} y={y(d.v)-11} textAnchor="middle" fontSize="10" fill={T.amber} fontWeight="700">{fmtRp(d.v)}</text>
          </>}
        </g>
      ))}

      {labels.map((l,i) => i%step===0 && (
        <text key={i} x={x(i)} y={H-8} textAnchor="middle" fontSize="9" fill={T.dim}>{l}</text>
      ))}
    </svg>
  )
}

// ─── Order funnel ─────────────────────────────────────────────────────────────
function OrderFunnel() {
  const total = orderFlow.reduce((s,d)=>s+d.n,0)
  const [hov, setHov] = useState<number|null>(null)
  return (
    <div style={{display:'flex', flexDirection:'column', gap:10}}>
      {orderFlow.map((d,i) => (
        <div key={i}
          style={{display:'flex', alignItems:'center', gap:10, cursor:'default'}}
          onMouseEnter={()=>setHov(i)} onMouseLeave={()=>setHov(null)}
        >
          <span style={{
            fontSize:11, width:72, textAlign:'right', flexShrink:0,
            fontFamily:"'DM Mono'",
            color: hov===i ? d.c : T.muted,
            transition:'color 0.15s',
          }}>{d.label}</span>
          <div style={{flex:1, height:20, borderRadius:99, overflow:'hidden', background:T.bg}}>
            <div style={{
              height:'100%', borderRadius:99,
              width:`${Math.max(4,(d.n/total)*100)}%`,
              background: hov===i ? d.c : `${d.c}60`,
              transition:'all 0.3s ease',
            }}/>
          </div>
          <span style={{
            fontSize:11, width:44, textAlign:'right', flexShrink:0,
            fontFamily:"'DM Mono'",
            color: hov===i ? T.text : T.muted,
            transition:'color 0.15s',
          }}>{d.n.toLocaleString('id')}</span>
        </div>
      ))}
    </div>
  )
}

// ─── Product card ─────────────────────────────────────────────────────────────
function ProductCard({ p }: { p: typeof mktProducts[0] }) {
  const [liked, setLiked] = useState(false)
  const [hov, setHov] = useState(false)
  const tagBg: Record<string,string> = { Bestseller:T.amber, Hot:T.rose, Baru:T.teal }
  return (
    <div
      style={{
        background:T.panel, borderRadius:16, overflow:'hidden',
        border:`1px solid ${hov ? T.borderHi : T.border}`,
        transform: hov ? 'translateY(-3px)' : 'translateY(0)',
        boxShadow: hov ? '0 8px 24px rgba(0,0,0,0.09)' : '0 1px 4px rgba(0,0,0,0.04)',
        transition:'all 0.2s ease', display:'flex', flexDirection:'column',
        cursor:'pointer',
      }}
      onMouseEnter={()=>setHov(true)} onMouseLeave={()=>setHov(false)}
    >
      <div style={{position:'relative', aspectRatio:'1/1', background:'#ede9e3', overflow:'hidden'}}>
        <img src={p.img} alt={p.name} style={{width:'100%',height:'100%',objectFit:'cover',transition:'transform 0.5s ease',transform: hov?'scale(1.06)':'scale(1)'}}/>
        <div style={{position:'absolute',inset:0,background:'linear-gradient(to top, rgba(0,0,0,0.28) 0%, transparent 55%)'}}/>
        {p.tag && (
          <span style={{
            position:'absolute', top:10, left:10,
            fontSize:10, fontWeight:700, padding:'3px 8px', borderRadius:20,
            background: tagBg[p.tag]||T.amber, color:'#fff',
          }}>{p.tag}</span>
        )}
        <button
          onClick={e=>{e.stopPropagation();setLiked(l=>!l)}}
          style={{
            position:'absolute', top:8, right:8,
            width:28, height:28, borderRadius:'50%',
            background:'rgba(255,255,255,0.85)',
            border:`1px solid ${liked ? T.rose+'60':'rgba(0,0,0,0.08)'}`,
            display:'flex', alignItems:'center', justifyContent:'center',
            cursor:'pointer', transition:'all 0.15s',
            backdropFilter:'blur(4px)',
          }}
        >
          <span style={{fontSize:12, color: liked ? T.rose : T.muted, lineHeight:1}}>{liked?'♥':'♡'}</span>
        </button>
        <div style={{position:'absolute', bottom:8, left:10, display:'flex', alignItems:'center', gap:3}}>
          <span style={{fontSize:11, color:'#fbbf24'}}>★</span>
          <span style={{fontSize:10, color:'rgba(255,255,255,0.9)', fontFamily:"'DM Mono'", fontWeight:500}}>{p.rating}</span>
        </div>
      </div>
      <div style={{padding:'14px', display:'flex', flexDirection:'column', gap:5, flex:1}}>
        <p style={{fontSize:12, fontWeight:600, color:T.text, lineHeight:1.4, display:'-webkit-box', WebkitLineClamp:2, WebkitBoxOrient:'vertical', overflow:'hidden'}}>{p.name}</p>
        <p style={{fontSize:11, color:T.muted}}>{p.store}</p>
        <div style={{display:'flex', alignItems:'flex-end', justifyContent:'space-between', marginTop:'auto', paddingTop:8}}>
          <span style={{fontSize:14, fontWeight:800, color:T.amber, fontFamily:"'DM Mono'"}}>{p.price}</span>
          <span style={{fontSize:10, color:T.dim, fontFamily:"'DM Mono'"}}>{p.sold.toLocaleString('id')} terjual</span>
        </div>
      </div>
    </div>
  )
}

// ─── App ──────────────────────────────────────────────────────────────────────
export default function App() {
  const [page, setPage] = useState<'Dashboard'|'Marketplace'>('Dashboard')
  const [period, setPeriod] = useState<Period>('Bulan')
  const [mktCat, setMktCat] = useState('Semua')
  const [mktQ, setMktQ] = useState('')

  const revData = revSeries[period]
  const revLbls = revLabels[period]

  const filtered = mktProducts.filter(p =>
    (mktCat==='Semua'||p.cat===mktCat) &&
    (!mktQ||p.name.toLowerCase().includes(mktQ.toLowerCase())||p.store.toLowerCase().includes(mktQ.toLowerCase()))
  )

  // shadow/card style shorthand
  const card = {
    background: T.panel,
    borderRadius: 16,
    border: `1px solid ${T.border}`,
    boxShadow: '0 1px 3px rgba(0,0,0,0.04)',
  }

  return (
    <div style={{display:'flex', minHeight:'100vh', background:T.bg, fontFamily:"'Outfit',sans-serif"}}>

      {/* ── Sidebar ── */}
      <aside style={{
        width:58, display:'flex', flexDirection:'column', alignItems:'center',
        paddingTop:16, gap:2,
        background:T.sidebar,
        borderRight:'none', flexShrink:0,
      }}>
        <div style={{
          width:34, height:34, borderRadius:10, marginBottom:18,
          background:`linear-gradient(135deg, ${T.amber}, #c2500a)`,
          display:'flex', alignItems:'center', justifyContent:'center',
          fontSize:15, fontWeight:800, color:'#fff',
        }}>S</div>

        {navItems.map(item => {
          const active = page===item.label
          return (
            <button key={item.label} title={item.label}
              onClick={()=>{ if(item.label==='Dashboard'||item.label==='Marketplace') setPage(item.label as any) }}
              style={{
                width:38, height:38, borderRadius:10,
                display:'flex', alignItems:'center', justifyContent:'center',
                fontSize:15,
                background: active ? 'rgba(224,124,40,0.2)' : 'transparent',
                color: active ? T.amber : 'rgba(255,255,255,0.35)',
                border:`1px solid ${active ? T.amber+'40':'transparent'}`,
                cursor:'pointer', transition:'all 0.15s',
              }}
            >{item.icon}</button>
          )
        })}

        <div style={{marginTop:'auto', marginBottom:14}}>
          <div style={{
            width:32, height:32, borderRadius:'50%',
            background:'linear-gradient(135deg, #7c5cbf, #4f46e5)',
            display:'flex', alignItems:'center', justifyContent:'center',
            fontSize:11, fontWeight:700, color:'#fff', cursor:'pointer',
          }}>TB</div>
        </div>
      </aside>

      {/* ── Main ── */}
      <div style={{flex:1, display:'flex', flexDirection:'column', minWidth:0, overflowY:'auto'}}>

        {/* Top bar */}
        <header style={{
          padding:'12px 24px',
          display:'flex', alignItems:'center', justifyContent:'space-between',
          background:T.surface,
          borderBottom:`1px solid ${T.border}`,
          position:'sticky', top:0, zIndex:10,
        }}>
          <div style={{display:'flex', gap:2}}>
            {(['Dashboard','Marketplace'] as const).map(p=>(
              <button key={p} onClick={()=>setPage(p)} style={{
                padding:'6px 16px', borderRadius:8, fontSize:13, fontWeight:600,
                background: page===p ? T.amber : 'transparent',
                color: page===p ? '#fff' : T.muted,
                border:'none', cursor:'pointer', transition:'all 0.15s',
              }}>{p}</button>
            ))}
          </div>

          <div style={{display:'flex', alignItems:'center', gap:10}}>
            {page==='Dashboard' && (
              <div style={{display:'flex', background:T.bg, borderRadius:8, padding:3, gap:2, border:`1px solid ${T.border}`}}>
                {periods.map(p=>(
                  <button key={p} onClick={()=>setPeriod(p)} style={{
                    padding:'5px 13px', borderRadius:6, fontSize:12, fontWeight:500,
                    background: period===p ? T.panel : 'transparent',
                    color: period===p ? T.text : T.muted,
                    border:`1px solid ${period===p ? T.border : 'transparent'}`,
                    boxShadow: period===p ? '0 1px 3px rgba(0,0,0,0.07)' : 'none',
                    cursor:'pointer', transition:'all 0.15s',
                  }}>{p}</button>
                ))}
              </div>
            )}
            <div style={{
              display:'flex', alignItems:'center', gap:6,
              padding:'6px 14px', borderRadius:8, fontSize:12, fontWeight:500,
              color:T.sub, background:T.bg, border:`1px solid ${T.border}`,
            }}>
              <span style={{width:7,height:7,borderRadius:'50%',background:T.green,display:'inline-block'}}/>
              TokoBagus
              <span style={{
                marginLeft:4, fontSize:10, fontWeight:700, color:T.amber,
                background:T.amberSoft, padding:'1px 6px', borderRadius:4,
              }}>PREMIUM</span>
            </div>
          </div>
        </header>

        {/* ══ DASHBOARD ══════════════════════════════════════════════════════ */}
        {page==='Dashboard' && (
          <main style={{padding:'24px', display:'flex', flexDirection:'column', gap:20}}>

            {/* Heading */}
            <div style={{display:'flex', alignItems:'flex-end', justifyContent:'space-between'}}>
              <div>
                <p style={{fontSize:11, color:T.muted, letterSpacing:'0.07em', textTransform:'uppercase', fontWeight:600, marginBottom:4}}>Ringkasan · Agustus 2026</p>
                <h1 style={{fontSize:24, fontWeight:800, color:T.text, lineHeight:1.2}}>Performa Toko</h1>
              </div>
              <p style={{fontSize:11, color:T.dim}}>Diperbarui 2 menit lalu</p>
            </div>

            {/* KPI grid */}
            <div style={{display:'grid', gridTemplateColumns:'repeat(6,1fr)', gap:10}}>
              {kpis.map((k,i)=>(
                <div key={i} style={{
                  ...card,
                  padding:'16px',
                  display:'flex', flexDirection:'column', gap:10,
                  transition:'box-shadow 0.2s, border-color 0.2s',
                }}
                  onMouseEnter={e=>{const el=e.currentTarget as HTMLDivElement; el.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)'; el.style.borderColor=T.borderHi}}
                  onMouseLeave={e=>{const el=e.currentTarget as HTMLDivElement; el.style.boxShadow='0 1px 3px rgba(0,0,0,0.04)'; el.style.borderColor=T.border}}
                >
                  <div style={{display:'flex', alignItems:'center', justifyContent:'space-between'}}>
                    <p style={{fontSize:11, color:T.muted, fontWeight:500}}>{k.label}</p>
                    <span style={{
                      width:28, height:28, borderRadius:8,
                      background:k.cs, display:'flex', alignItems:'center', justifyContent:'center',
                      fontSize:13,
                    }}>
                      {['💰','📦','👤','📈','🧾','🏦'][i]}
                    </span>
                  </div>
                  <p style={{fontSize:20, fontWeight:800, color:T.text, fontFamily:"'DM Mono'", lineHeight:1}}>{k.value}</p>
                  <div style={{display:'flex', alignItems:'center', justifyContent:'space-between'}}>
                    <span style={{
                      fontSize:10, fontWeight:700, fontFamily:"'DM Mono'",
                      color: k.up===null ? T.teal : k.up ? T.green : T.rose,
                    }}>
                      {k.up!==null&&(k.up?'↑ ':'↓ ')}{k.delta}
                    </span>
                    <Spark data={revSeries[period].slice(-8).map(d=>d.v)} color={k.c}/>
                  </div>
                  <p style={{fontSize:10, color:T.dim}}>{k.sub}</p>
                </div>
              ))}
            </div>

            {/* Revenue chart */}
            <div style={{...card, padding:'22px 20px 14px'}}>
              <div style={{display:'flex', alignItems:'center', justifyContent:'space-between', marginBottom:16}}>
                <div>
                  <h2 style={{fontSize:14, fontWeight:700, color:T.text}}>Revenue</h2>
                  <p style={{fontSize:11, color:T.muted, marginTop:2}}>
                    Pendapatan bersih per {period==='Hari'?'hari':period==='Minggu'?'minggu':'bulan'}
                  </p>
                </div>
                <div style={{display:'flex', alignItems:'center', gap:6}}>
                  <span style={{width:14, height:2, background:T.amber, borderRadius:2, display:'inline-block'}}/>
                  <span style={{fontSize:11, color:T.muted}}>Revenue</span>
                </div>
              </div>
              <RevenueChart data={revData} labels={revLbls}/>
            </div>

            {/* Middle: funnel + sellers + wallet */}
            <div style={{display:'grid', gridTemplateColumns:'1fr 1fr 236px', gap:16}}>

              {/* Funnel */}
              <div style={{...card, padding:'20px'}}>
                <h2 style={{fontSize:14, fontWeight:700, color:T.text, marginBottom:3}}>Alur Pesanan</h2>
                <p style={{fontSize:11, color:T.muted, marginBottom:18}}>Status realtime semua order</p>
                <OrderFunnel/>
                <div style={{
                  marginTop:18, padding:'12px 14px', borderRadius:10,
                  background:T.amberSoft, border:`1px solid ${T.amber}25`,
                  display:'flex', justifyContent:'space-between', alignItems:'center',
                }}>
                  <span style={{fontSize:12, color:T.amber, fontWeight:600}}>Total order bulan ini</span>
                  <span style={{fontSize:16, fontWeight:800, color:T.text, fontFamily:"'DM Mono'"}}>{orderFlow.reduce((s,d)=>s+d.n,0).toLocaleString('id')}</span>
                </div>
              </div>

              {/* Top sellers */}
              <div style={{...card, padding:'20px'}}>
                <h2 style={{fontSize:14, fontWeight:700, color:T.text, marginBottom:3}}>Top Seller</h2>
                <p style={{fontSize:11, color:T.muted, marginBottom:14}}>Berdasarkan GMV bulan ini</p>
                <div style={{display:'flex', flexDirection:'column', gap:8}}>
                  {topSellers.map((s,i)=>(
                    <div key={i} style={{
                      display:'flex', alignItems:'center', gap:10,
                      padding:'10px 12px', borderRadius:10,
                      background: i===0 ? T.amberSoft : T.bg,
                      border:`1px solid ${i===0 ? T.amber+'25' : T.border}`,
                      transition:'background 0.2s',
                      cursor:'default',
                    }}>
                      <span style={{fontSize:10, fontWeight:700, color:T.dim, width:14, textAlign:'center', fontFamily:"'DM Mono'"}}>{i+1}</span>
                      <div style={{
                        width:32, height:32, borderRadius:'50%', flexShrink:0,
                        background:`hsl(${s.hue},48%,72%)`,
                        display:'flex', alignItems:'center', justifyContent:'center',
                        fontSize:11, fontWeight:800, color:`hsl(${s.hue},48%,28%)`,
                      }}>{s.avatar}</div>
                      <div style={{flex:1, minWidth:0}}>
                        <p style={{fontSize:12, fontWeight:600, color:T.text, whiteSpace:'nowrap', overflow:'hidden', textOverflow:'ellipsis'}}>{s.name}</p>
                        <p style={{fontSize:10, color:T.muted}}>{s.orders} pesanan · ★ {s.rating}</p>
                      </div>
                      <div style={{textAlign:'right', flexShrink:0}}>
                        <p style={{fontSize:12, fontWeight:700, color:T.amber, fontFamily:"'DM Mono'"}}>{s.gmv}</p>
                        {s.badge && (
                          <span style={{
                            fontSize:9, fontWeight:700, textTransform:'uppercase',
                            color: s.badge==='top'?T.amber:T.violet,
                            background: s.badge==='top'?T.amberSoft:T.violetSoft,
                            padding:'1px 5px', borderRadius:4,
                          }}>{s.badge}</span>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Wallet */}
              <div style={{
                borderRadius:16, padding:'22px 20px',
                background:`linear-gradient(155deg, #fdf6ee 0%, #fef9f3 100%)`,
                border:`1px solid ${T.amber}30`,
                display:'flex', flexDirection:'column', gap:16,
              }}>
                <div style={{display:'flex', alignItems:'center', justifyContent:'space-between'}}>
                  <span style={{fontSize:13, fontWeight:700, color:T.text}}>Dompet Seller</span>
                  <span style={{
                    fontSize:9, fontWeight:700, textTransform:'uppercase', letterSpacing:'0.05em',
                    color:T.amber, background:T.amberSoft, padding:'3px 8px', borderRadius:6,
                    border:`1px solid ${T.amber}30`,
                  }}>Premium</span>
                </div>

                <div>
                  <p style={{fontSize:10, color:T.muted, marginBottom:4}}>Saldo Tersedia</p>
                  <p style={{fontSize:26, fontWeight:800, color:T.text, fontFamily:"'DM Mono'", lineHeight:1}}>Rp 38.4Jt</p>
                </div>

                <div style={{background:'rgba(224,124,40,0.07)', borderRadius:10, padding:'11px 13px', border:`1px solid ${T.amber}15`}}>
                  <p style={{fontSize:10, color:T.muted, marginBottom:3}}>Pending Escrow</p>
                  <p style={{fontSize:16, fontWeight:700, color:'#d97706', fontFamily:"'DM Mono'"}}> Rp 6.2Jt</p>
                </div>

                <div style={{display:'flex', flexDirection:'column', gap:7}}>
                  {[
                    ['Total Withdraw', 'Rp 122.7Jt', T.sub],
                    ['Withdraw Terakhir', '3 Agu 2026', T.sub],
                    ['Fee Withdraw', 'Gratis ✓', T.green],
                  ].map(([k,v,c])=>(
                    <div key={k} style={{display:'flex', justifyContent:'space-between', alignItems:'center'}}>
                      <span style={{fontSize:10, color:T.muted}}>{k}</span>
                      <span style={{fontSize:11, fontWeight:600, color:c, fontFamily:k==='Fee Withdraw'?'inherit':"'DM Mono'"}}>{v}</span>
                    </div>
                  ))}
                </div>

                <button style={{
                  background:`linear-gradient(90deg, #e07c28, #c2500a)`,
                  color:'#fff', border:'none', borderRadius:10,
                  padding:'11px 0', fontSize:13, fontWeight:700, cursor:'pointer',
                  width:'100%', transition:'opacity 0.15s',
                  boxShadow:'0 2px 8px rgba(224,124,40,0.25)',
                }}>Tarik Dana</button>
              </div>
            </div>
          </main>
        )}

        {/* ══ MARKETPLACE ════════════════════════════════════════════════════ */}
        {page==='Marketplace' && (
          <main style={{padding:'24px', display:'flex', flexDirection:'column', gap:20}}>

            {/* Hero */}
            <div style={{
              borderRadius:20, overflow:'hidden', position:'relative',
              background:`linear-gradient(120deg, #fdf0e4 0%, #fef8f0 55%, #f5f4f0 100%)`,
              border:`1px solid ${T.amber}25`,
              padding:'28px 32px',
              display:'flex', alignItems:'center', justifyContent:'space-between',
            }}>
              <div style={{
                position:'absolute', top:-40, right:120,
                width:280, height:280, borderRadius:'50%',
                background:`radial-gradient(circle, ${T.amber}18 0%, transparent 70%)`,
                pointerEvents:'none',
              }}/>
              <div style={{position:'relative'}}>
                <p style={{fontSize:11, color:T.amber, fontWeight:700, letterSpacing:'0.08em', textTransform:'uppercase', marginBottom:6}}>Platform Marketplace</p>
                <h1 style={{fontSize:26, fontWeight:800, color:T.text, lineHeight:1.25}}>
                  Temukan produk terbaik<br/>
                  <span style={{color:T.amber}}>dari ribuan toko</span>
                </h1>
                <p style={{fontSize:12, color:T.muted, marginTop:8}}>8.341 produk · 1.240 toko aktif · 54.921 pengunjung hari ini</p>
              </div>
              <div style={{position:'relative', display:'flex', flexDirection:'column', gap:10, alignItems:'flex-end'}}>
                <div style={{
                  display:'flex', alignItems:'center', gap:8,
                  background:T.panel, border:`1px solid ${T.border}`,
                  borderRadius:12, padding:'10px 16px', width:300,
                  boxShadow:'0 2px 8px rgba(0,0,0,0.06)',
                }}>
                  <span style={{fontSize:14, color:T.dim}}>⌕</span>
                  <input
                    value={mktQ}
                    onChange={e=>setMktQ(e.target.value)}
                    placeholder="Cari produk atau toko..."
                    style={{flex:1, background:'transparent', border:'none', outline:'none', fontSize:13, color:T.text}}
                  />
                  {mktQ && <button onClick={()=>setMktQ('')} style={{fontSize:12, color:T.muted, background:'none', border:'none', cursor:'pointer'}}>✕</button>}
                </div>
                <div style={{display:'flex', gap:6}}>
                  {['🔥 Flash Sale','✨ Produk Baru','🚚 Gratis Ongkir'].map(tag=>(
                    <span key={tag} style={{
                      fontSize:11, fontWeight:500, padding:'5px 11px', borderRadius:20,
                      background:T.panel, color:T.sub,
                      border:`1px solid ${T.border}`, cursor:'pointer',
                      boxShadow:'0 1px 3px rgba(0,0,0,0.05)',
                    }}>{tag}</span>
                  ))}
                </div>
              </div>
            </div>

            {/* Stats strip */}
            <div style={{display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:10}}>
              {[
                {label:'Total Produk',     val:'8.341',  icon:'⬡', c:T.amber,  cs:T.amberSoft },
                {label:'Toko Aktif',       val:'1.240',  icon:'◉', c:T.sky,    cs:T.skySoft   },
                {label:'Terjual Hari Ini', val:'312',    icon:'↑', c:T.green,  cs:T.greenSoft },
                {label:'Pengunjung',       val:'54.921', icon:'◎', c:T.violet, cs:T.violetSoft},
              ].map(s=>(
                <div key={s.label} style={{...card, padding:'14px 16px', display:'flex', alignItems:'center', gap:12}}>
                  <span style={{
                    width:36, height:36, borderRadius:10, fontSize:15,
                    background:s.cs, border:`1px solid ${s.c}20`,
                    display:'flex', alignItems:'center', justifyContent:'center',
                    color:s.c, flexShrink:0,
                  }}>{s.icon}</span>
                  <div>
                    <p style={{fontSize:18, fontWeight:800, color:T.text, fontFamily:"'DM Mono'", lineHeight:1}}>{s.val}</p>
                    <p style={{fontSize:10, color:T.muted, marginTop:2}}>{s.label}</p>
                  </div>
                </div>
              ))}
            </div>

            {/* Category + sort */}
            <div style={{display:'flex', alignItems:'center', gap:6, flexWrap:'wrap'}}>
              {mktCats.map(cat=>(
                <button key={cat} onClick={()=>setMktCat(cat)} style={{
                  padding:'7px 16px', borderRadius:20, fontSize:12, fontWeight:500,
                  background: mktCat===cat ? T.amber : T.panel,
                  color: mktCat===cat ? '#fff' : T.sub,
                  border:`1px solid ${mktCat===cat ? T.amber : T.border}`,
                  cursor:'pointer', transition:'all 0.15s',
                  boxShadow: mktCat===cat ? '0 2px 8px rgba(224,124,40,0.2)' : 'none',
                }}>{cat}</button>
              ))}
              <div style={{marginLeft:'auto', display:'flex', gap:5}}>
                {['Terlaris','Terbaru','Harga ↑','Harga ↓'].map(s=>(
                  <button key={s} style={{
                    padding:'6px 11px', borderRadius:8, fontSize:11, fontWeight:500,
                    background:T.panel, color:T.muted,
                    border:`1px solid ${T.border}`, cursor:'pointer',
                  }}>{s}</button>
                ))}
              </div>
            </div>

            {/* Product grid */}
            <div>
              <div style={{display:'flex', alignItems:'center', justifyContent:'space-between', marginBottom:12}}>
                <h2 style={{fontSize:14, fontWeight:700, color:T.text}}>
                  {mktCat==='Semua'?'Semua Produk':mktCat}
                  <span style={{fontSize:12, color:T.muted, fontWeight:400, marginLeft:6}}>{filtered.length} produk</span>
                </h2>
              </div>
              {filtered.length>0 ? (
                <div style={{display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(190px,1fr))', gap:12}}>
                  {filtered.map(p=><ProductCard key={p.id} p={p}/>)}
                </div>
              ) : (
                <div style={{textAlign:'center', padding:'56px 0', color:T.muted}}>
                  <p style={{fontSize:28, marginBottom:8}}>⊡</p>
                  <p style={{fontSize:14, fontWeight:600, color:T.dim}}>Produk tidak ditemukan</p>
                  <p style={{fontSize:12, marginTop:4}}>Coba kata kunci atau kategori lain</p>
                </div>
              )}
            </div>

            {/* Top stores */}
            <div>
              <h2 style={{fontSize:14, fontWeight:700, color:T.text, marginBottom:12}}>Toko Unggulan</h2>
              <div style={{display:'grid', gridTemplateColumns:'repeat(5,1fr)', gap:10}}>
                {topSellers.map((s,i)=>(
                  <div key={i} style={{
                    ...card, padding:'20px 16px', textAlign:'center',
                    cursor:'pointer', transition:'all 0.2s',
                  }}
                    onMouseEnter={e=>{const el=e.currentTarget as HTMLDivElement; el.style.boxShadow='0 6px 20px rgba(0,0,0,0.08)'; el.style.transform='translateY(-2px)'; el.style.borderColor=T.borderHi}}
                    onMouseLeave={e=>{const el=e.currentTarget as HTMLDivElement; el.style.boxShadow='0 1px 3px rgba(0,0,0,0.04)'; el.style.transform='translateY(0)'; el.style.borderColor=T.border}}
                  >
                    <div style={{
                      width:48, height:48, borderRadius:'50%', margin:'0 auto 10px',
                      background:`hsl(${s.hue},48%,88%)`,
                      border:`2px solid hsl(${s.hue},48%,72%)`,
                      display:'flex', alignItems:'center', justifyContent:'center',
                      fontSize:14, fontWeight:800, color:`hsl(${s.hue},48%,28%)`,
                    }}>{s.avatar}</div>
                    <p style={{fontSize:12, fontWeight:700, color:T.text, marginBottom:2}}>{s.name}</p>
                    <p style={{fontSize:10, color:T.muted, marginBottom:8}}>{s.orders} terjual</p>
                    <div style={{display:'flex', alignItems:'center', justifyContent:'center', gap:3}}>
                      <span style={{fontSize:10, color:'#f59e0b'}}>★</span>
                      <span style={{fontSize:11, fontWeight:600, color:T.sub, fontFamily:"'DM Mono'"}}>{s.rating}</span>
                    </div>
                    {s.badge && (
                      <div style={{marginTop:8}}>
                        <span style={{
                          fontSize:9, fontWeight:700, textTransform:'uppercase',
                          background: s.badge==='top'?T.amber:T.violet,
                          color:'#fff', padding:'2px 8px', borderRadius:20,
                        }}>{s.badge}</span>
                      </div>
                    )}
                  </div>
                ))}
              </div>
            </div>

          </main>
        )}
      </div>
    </div>
  )
}

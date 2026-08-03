// validation.js - small form helpers
export function isEmail(v){ return /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v); }
export function passwordScore(pw){ let s=0; if(pw.length>=8) s++; if(/[A-Z]/.test(pw)) s++; if(/[0-9]/.test(pw)) s++; if(/[^A-Za-z0-9]/.test(pw)) s++; return s; }

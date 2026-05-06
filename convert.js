const fs = require('fs');
const file = 'c:\\laragon\\www\\Cafeteria Vs.2 - PWA\\cafeteria-web\\schema.sql';
let content = fs.readFileSync(file, 'utf8');

// Remove comments
content = content.replace(/^--.*$/gm, '');

// Remove SET, \restrict, \unrestrict, \.
content = content.replace(/^SET .*;\s*$/gm, '');
content = content.replace(/^SELECT pg_catalog\.set_config.*\s*$/gm, '');
content = content.replace(/^\\restrict.*\s*$/gm, '');
content = content.replace(/^\\unrestrict.*\s*$/gm, '');
content = content.replace(/^ALTER .* OWNER TO .*;\s*$/gm, '');

let lines = content.split('\n');
let outLines = [];
let inCopy = false;
let copyTable = '';
let copyCols = '';

for(let line of lines) {
    if (line.startsWith('COPY ')) {
        inCopy = true;
        let match = line.match(/^COPY\s+(.*?)\s+\((.*?)\)\s+FROM\s+stdin;/i);
        if (match) {
            copyTable = match[1];
            copyCols = match[2];
        } else {
             let match2 = line.match(/^COPY\s+(.*?)\s+FROM\s+stdin;/i);
             if (match2) {
                 copyTable = match2[1];
                 copyCols = '';
             }
        }
        continue;
    }
    
    if (inCopy) {
        if (line.trim() === '\\\\.') {
            inCopy = false;
            continue;
        }
        if (line.trim() === '\\.') {
            inCopy = false;
            continue;
        }
        if (line.trim() === '') continue;
        
        let values = line.split('\t');
        let formattedValues = values.map(v => {
            if (v === '\\\\N') return 'NULL';
            if (v === '\\N') return 'NULL';
            v = v.replace(/\\\\\\\\/g, '\\\\');
            v = v.replace(/\\\\n/g, '\\n');
            v = v.replace(/\\\\r/g, '\\r');
            v = v.replace(/\\\\t/g, '\\t');
            v = v.replace(/'/g, "''");
            return "'" + v + "'";
        });
        
        if (copyCols) {
            outLines.push(`INSERT INTO ${copyTable} (${copyCols}) VALUES (${formattedValues.join(', ')});`);
        } else {
            outLines.push(`INSERT INTO ${copyTable} VALUES (${formattedValues.join(', ')});`);
        }
        continue;
    }
    
    outLines.push(line);
}

let finalOut = outLines.filter(l => l.trim() !== '').join('\n');
fs.writeFileSync(file, finalOut);
console.log('Done!');

export const // 

    addZeros = (str, maxlen = 2) => {
        str = '' + str;
        while (str.length < maxlen)
            str = "0" + str;
        return str;
    },

    time = (t = 0) => {

        const // 
            cb = typeof t === 'number' ? (o) => o : t,
            time = typeof t === 'number' ? t : 0,
            date = time ? new Date(time) : new Date(),
            Y = '' + date.getFullYear(),
            M = addZeros(date.getMonth() + 1, 2),
            D = addZeros(date.getDate(), 2),
            H = addZeros(date.getHours(), 2),
            m = addZeros(date.getMinutes(), 2),
            s = addZeros(date.getSeconds(), 2);
        return cb({ Y, M, D, H, m, s });
    },

    add1Day = (date) => time(+new Date(Date.parse(date) + (3600 * 1000 * 24))),

    subMinutes = (date, n) => time(+new Date(Date.parse(date) - (60 * 1000 * n))),

    addMinutes = (date, n) => time(+new Date(Date.parse(date) + (60 * 1000 * n))),

    isBetween = (limiteDebut, limiteFin) => (sDebut) => {
        const // 
            Debut = typeof sDebut === 'string' ? sDebut : sDebut.debut,
            Fin = typeof sDebut === 'string' ? sDebut : (sDebut.fin || sDebut.debut),
            limiteBasse = +limiteDebut.split('-').join(''),
            limiteHaute = +limiteFin.split('-').join(''),
            [debut] = Debut.split(/\s|T/),
            [fin] = Fin.split(/\s|T/),
            intDebut = +debut.split('-').join(''),
            intFin = +fin.split('-').join('');
        if (intFin < limiteBasse)
            return false;
        if (intDebut > limiteHaute)
            return false;
        return true;
    }
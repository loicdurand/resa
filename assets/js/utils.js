export const // 

    addZeros = (str, maxlen = 2) => {
        str = '' + str;
        while (str.length < maxlen)
            str = "0" + str;
        return str;
    };
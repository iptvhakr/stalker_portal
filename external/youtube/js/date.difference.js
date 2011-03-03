Date.prototype.Difference = function (fromDate) {
    var tmp_date = fromDate.split("T")[0].split("-");
    var tmp_time = fromDate.split("T")[1].split(".")[0].split(":");
    var tmp_workDT = new Date(tmp_date[0], tmp_date[1], tmp_date[2], tmp_time[0], tmp_time[1], tmp_time[2]);
    var tmp_curDT = new Date();
    var years =( Math.round(tmp_workDT.getYearsBetween(tmp_curDT)) - 1>=0) ?  Math.round(tmp_workDT.getYearsBetween(tmp_curDT)) - 1 : 0;
    var months = Math.round(tmp_workDT.getMonthsBetween(tmp_curDT));
    var weeks = Math.round(tmp_curDT.getWeekDays(tmp_workDT));
    var days = Math.round(tmp_workDT.getDaysBetween(tmp_curDT));
    //log("Source date: " + fromDate + " ::: years: " + years + "| months: " + months + "| weeks: " + weeks + "| days: " + days);
    if(days < 0) {
        return fromDate.split("T")[0];
    }
    switch(years) {
        case 0:
            switch(months) {
                case 0:
                    switch(weeks) {
                        case 0:
                            switch(days) {
                                case 0:
                                    return lang.time.today;
                                break;
                                case 1:
                                    return lang.time.yesterday;
                                break;
                                case 2:
                                case 3:
                                case 4:
                                    return days.toString() + lang.time.days2_4 + lang.time.ago;
                                break;
                                default:
                                    return days.toString() + lang.time.days5_more + lang.time.ago;
                                break;
                            }
                        break;
                        case 1:
                            return lang.time.week + lang.time.ago;
                        break;
                        case 2:
                        case 3:
                        case 4:
                            return weeks.toString() + lang.time.weeks2_4 + lang.time.ago;
                        break;
                        default:
                            return weeks.toString() + lang.time.weeks5_more + lang.time.ago;
                        break;
                    }
                break;
                case 1:
                    return lang.time.month + lang.time.ago;
                break;
                case 2:
                case 3:
                case 4:
                    return months.toString() + lang.time.months2_4 + lang.time.ago;
                break;
                default:
                    return months.toString() + lang.time.months5_more + lang.time.ago;
                break;
            }
        break;
        case 1:
            return lang.time.year + lang.time.ago;
        break;
        case 2:
        case 3:
        case 4:
            return years.toString() + lang.time.years2_4 + lang.time.ago;
        break;
        default:
            return years.toString() + lang.time.years5_more + lang.time.ago;
        break;
    }
};

/*= = = = = = = = = = = = = = = = = = = = = = = =
= = = = = = = = = = = = = = = = = = = = = = = = =
= = = = = = = = = = = = = = = = = = = = = = = =*/
Date.prototype.msPERDAY = 24 * 60 * 60 * 1000;
Date.prototype.copy = function () {
  return new Date(this.getTime());
};
Date.prototype.to12HourTimeString = function () { //return in 12 hours format
    var h = this.getHours();
    var m = "0" + this.getMinutes();
    var s = "0" + this.getSeconds();
    var ap = "am";

    if (h >= 12) {
        ap = "pm";
        if (h >= 13)
            h -= 12;
    } else {
        if (h == 0)
            h = 12;
    }
    h = "0" + h;
    return h.slice(-2) + ":" + m.slice(-2) + ":" + s.slice(-2) + " " + ap;
};
Date.prototype.to24HourTimeString = function () { //return in 24 hours format
  var h = "0" + this.getHours();
  var m = "0" + this.getMinutes();
  var s = "0" + this.getSeconds();
  return h.slice(-2) + ":" + m.slice(-2) + ":" + s.slice(-2);
};
Date.prototype.countYearsDays = function() { // count days in year
  var d = new Date(this.getFullYear(), this.getMonth() + 1, 0);
  return d.getDate();
};
Date.prototype.addDays = function(d) {  // function add days
    this.setDate( this.getDate() + d );
};
Date.prototype.addWeeks = function(w) {  // function add weeks
    this.addDays(w * 7);
};
Date.prototype.addMonths= function(m) {  // function add months
    var d = this.getDate();
    this.setMonth(this.getMonth() + m);

    if (this.getDate() < d)
        this.setDate(0);
};
Date.prototype.addYears = function(y) {  // function add years
    var m = this.getMonth();
    this.setFullYear(this.getFullYear() + y);

    if (m < this.getMonth()) {
        this.setDate(0);
    }
};
Date.prototype.addWorkDays = function(d) {
  var startDay = this.getDay();  //текущий день недели от 0 до 6
  var wkEnds = 0;  //число нужных выходных
  var partialWeek = d % 5;  //количество дней для неполной недели

  if (d < 0) {  //вычитание дней недели
    wkEnds = Math.ceil(d/5); //отрицательное количество выходных

    switch (startDay) {
    case 6:  //начинаем с субботы, на 1 выходной меньше
      if (partialWeek == 0 && wkEnds < 0)
        wkEnds++;
      break;
    case 0:  //начальный день - воскресенье
      if (partialWeek == 0)
        d++;  //уменьшаем добавленные дни
      else
        d--;  // увеличиваем добавленные дни
      break;
    default:
      if (partialWeek <= -startDay)
      wkEnds--;
    }
  }
  else if (d > 0) {  //adding weekdays
    wkEnds = Math.floor(d/5);
    var w = wkEnds;
    switch (startDay) {
    case 6:
      // Если начальный день – суббота и
      // неделя полная, нужно уменьшить дни на 1
      // неделя неполная, нужно увеличить дни на 1
      if (partialWeek == 0)
        d--;
      else
        d++;
      break;
    case 0:
      //Sunday
      if (partialWeek == 0 && wkEnds > 0)
        wkEnds--;
      break;
    default:
      if (5 - day < partialWeek)
        wkEnds++;
    }
  }

  d += wkEnds * 2;
  this.addDays(d);
};
Date.prototype.getDaysBetween = function(d) {
  var d2;

  // дополнительный код для свойств аргументов
  if (arguments.length == 0) {
    d2 = new Date();
  } else if (d instanceof Date) {
    d2 = new Date(d.getTime());
  } else if (typeof d == "string") {
    d2 = new Date(d);
  } else if (arguments.length >= 3) {
    var dte = [0, 0, 0, 0, 0, 0];
    for (var i = 0; i < arguments.length; i++) {
      dte  [i] = arguments[i];
    }
    d2 = new Date(dte[0], dte[1], dte[2], dte[3], dte[4], dte[5]);
  } else if (typeof d == "number") {
    d2 = new Date(d);
  } else {
    return null;
  }
  //log(d2 + " - 194 line")
  if (d2 == "Invalid Date")
    return null;
  // Конец дополнительного кода

  d2.setHours(this.getHours(), this.getMinutes(), this.getSeconds(), this.getMilliseconds());
  //log(d2 + " - 211 line")
  var diff = d2.getTime() - this.getTime();
  //log(diff + " - 211 line")
  return (diff)/this.msPERDAY;
};
Date.prototype.getWeekDays = function(d) {
    var wkEnds = 0;
    var days = Math.abs(this.getDaysBetween(d));
    var startDay = 0, endDay = 0;

    if (days) {
        if (d < this) {
            startDay = d.getDay();
            endDay = this.getDay();
        } else {
            startDay = this.getDay();
            endDay = d.getDay();
        }
        wkEnds = Math.floor(days/7);

        if (startDay != 6 && startDay > endDay)
            wkEnds++;

        if (startDay != endDay && (startDay == 6 || endDay == 6) )
            days--;

        days -= (wkEnds * 2);
    }
    return days;
};
Date.prototype.getMonthsBetween = function(d) {
  var sDate, eDate;
  var d1 = this.getFullYear() * 12 + this.getMonth();
  var d2 = d.getFullYear() * 12 + d.getMonth();
  var sign;
  var months = 0;

  if (this == d) {
    months = 0;
  } else if (d1 == d2) { //тот же год и месяц
    months = (d.getDate() - this.getDate()) / this.countYearsDays();
  } else {
    if (d1 <  d2) {
      sDate = this;
      eDate = d;
      sign = 1;
    } else {
      sDate = d;
      eDate = this;
      sign = -1;
    }

    var sAdj = sDate.countYearsDays() - sDate.getDate();
    var eAdj = eDate.getDate();
    var adj = (sAdj + eAdj) / sDate.countYearsDays() - 1;
    months = Math.abs(d2 - d1) + adj;
    months = (months * sign)
  }
  return months;
};
Date.prototype.getYearsBetween = function(d) {
  var months = this.getMonthsBetween(d);
  return months/12;
};
Date.prototype.getAge = function() {
  var today = new Date();
  return this.getYearsBetween(today).toFixed(2);
};
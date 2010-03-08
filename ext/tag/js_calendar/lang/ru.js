// full day names
Calendar._DN = new Array
("Воскресенье",
 "Понедельник",
 "Вторник",
 "Среда",
 "Четверг",
 "Пятница",
 "Субота",
 "Воскресенье");

// short day names
Calendar._SDN = new Array
("Вс",
 "Пн",
 "Вт",
 "Ср",
 "Чт",
 "Пт",
 "Сб",
 "Вс");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 1;

// full month names
Calendar._MN = new Array
("Январь",
 "Февраль",
 "Март",
 "Апрель",
 "Май",
 "Июнь",
 "Июль",
 "Август",
 "Сентябрь",
 "Октябрь",
 "Ноябрь",
 "Декабрь");

// short month names
// я хз как они сокращаются у нас, сделал как у "Висты"
Calendar._SMN = new Array
("Янв",
 "Фев",
 "Мар",
 "Апр",
 "Май",
 "Июн",
 "Июл",
 "Авг",
 "Сен",
 "Окт",
 "Ноя",
 "Дек");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "Помощь";

Calendar._TT["ABOUT"] =
"DHTML Date/Time Selector\n" +
"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
"For latest version visit:\n" +
"http://www.dynarch.com/projects/calendar/\n" +
"Distributed under GNU LGPL:\n" +
"http://gnu.org/licenses/lgpl.html" +
"\n\n" +
"Выбор даты:\n" +
"- используйте \xab, \xbb для выбора года;\n" +
"- используйте " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " для выбора месяца;\n" +
"- удерживайте кнопку мышки на меню для быстрого выбора месяца или года.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Выбор времени:\n" +
"- кликните на часы или секнды для увеличения значения\n" +
"- SHIFT+клик - для уменьшения,\n" +
"- также можно удерживайте кнопку мышки и направлять мышь влево или вправо для изменения значения.";

Calendar._TT["PREV_YEAR"] = "Предыдущий год";
Calendar._TT["PREV_MONTH"] = "Предыдущий месяц";
Calendar._TT["GO_TODAY"] = "Сегодня";
Calendar._TT["NEXT_MONTH"] = "Следующий месяц";
Calendar._TT["NEXT_YEAR"] = "Следующий год";
Calendar._TT["SEL_DATE"] = "Выбрать дату";
Calendar._TT["DRAG_TO_MOVE"] = "Окно можно перетаскивать";
Calendar._TT["PART_TODAY"] = " (сегодня)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "%s как начало недели";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Закрыть";
Calendar._TT["TODAY"] = "Сегодня";
Calendar._TT["TIME_PART"] = "(SHIFT+)клик или перемещение мыши";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%d.%m.%Y";
Calendar._TT["TT_DATE_FORMAT"] = "%A, %b %e";

Calendar._TT["WK"] = "№";
Calendar._TT["TIME"] = "Время:";

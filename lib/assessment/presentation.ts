export function riskLabel(score: number) {
  if (score < 35) {
    return { label: "Laag risico", className: "label-low" };
  }

  if (score < 70) {
    return { label: "Middelgroot risico", className: "label-medium" };
  }

  return { label: "Hoog risico", className: "label-high" };
}

export function categoryLabel(category: string) {
  switch (category) {
    case "tripping_hazard":
      return "Struikelgevaar";
    case "slip_hazard":
      return "Glijgevaar";
    case "support_hazard":
      return "Onvoldoende houvast";
    case "lighting_hazard":
      return "Verlichting & oriëntatie";
    case "accessibility_hazard":
      return "Toegankelijkheid";
    default:
      return "Overig";
  }
}

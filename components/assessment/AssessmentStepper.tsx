type AssessmentStepKey = "choose" | "upload" | "result";

type AssessmentStepperProps = {
  currentStep: AssessmentStepKey;
};

type StepItem = {
  key: AssessmentStepKey;
  label: string;
};

const STEPS: StepItem[] = [
  { key: "choose", label: "Kies ruimte" },
  { key: "upload", label: "Upload foto's" },
  { key: "result", label: "Resultaat" }
];

const STEP_INDEX: Record<AssessmentStepKey, number> = {
  choose: 0,
  upload: 1,
  result: 2
};

export function AssessmentStepper({ currentStep }: AssessmentStepperProps) {
  const currentIndex = STEP_INDEX[currentStep];

  return (
    <nav className="assessment-stepper" aria-label="Voortgang foto-assessment">
      <ol className="assessment-stepper-list">
        {STEPS.map((step, index) => {
          const status =
            index < currentIndex ? "completed" : index === currentIndex ? "active" : "upcoming";

          return (
            <li key={step.key} className={`assessment-step ${status}`}>
              <span className="assessment-step-index" aria-hidden="true">
                {index + 1}
              </span>
              <span className="assessment-step-label">{step.label}</span>
            </li>
          );
        })}
      </ol>
    </nav>
  );
}

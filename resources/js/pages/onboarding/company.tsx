import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/onboarding';

type Props = {
    company: {
        name: string;
        document: string | null;
        phone: string | null;
        address: string | null;
    };
};

export default function OnboardingCompany({ company }: Props) {
    return (
        <>
            <Head title="Complete your company profile" />
            <Form
                {...update.form()}
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <div className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="document">Tax ID (CNPJ)</Label>
                            <Input
                                id="document"
                                type="text"
                                required
                                autoFocus
                                tabIndex={1}
                                name="document"
                                defaultValue={company.document ?? ''}
                                placeholder="00.000.000/0000-00"
                            />
                            <InputError message={errors.document} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="phone">Phone</Label>
                            <Input
                                id="phone"
                                type="text"
                                required
                                tabIndex={2}
                                name="phone"
                                defaultValue={company.phone ?? ''}
                                placeholder="(00) 00000-0000"
                            />
                            <InputError message={errors.phone} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="address">Address</Label>
                            <Input
                                id="address"
                                type="text"
                                required
                                tabIndex={3}
                                name="address"
                                defaultValue={company.address ?? ''}
                                placeholder="Street, number, city"
                            />
                            <InputError message={errors.address} />
                        </div>

                        <Button
                            type="submit"
                            className="mt-2 w-full"
                            tabIndex={4}
                        >
                            {processing && <Spinner />}
                            Finish setup
                        </Button>
                    </div>
                )}
            </Form>
        </>
    );
}

OnboardingCompany.layout = {
    title: 'Complete your company profile',
    description: 'A few more details about your agency before you continue',
};

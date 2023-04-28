import Link from "next/link"

export default function BlogPostCard({ post }) {
    const { title, category, image, date, slug, excerpt } = post
    const formatDate = () => {
        const months = [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December",
        ];
        const newDate = new Date(date);
        const day = newDate.getDate();
        const monthIndex = newDate.getMonth();
        const month = months[monthIndex];

        return { day, month }
    }
    return (
        <div className="item mb-80">
            <div className="img">
                <Link href={"/blog/" + slug.current}>
                    <a>
                        <img src={image.asset.url} alt="" />
                    </a>
                </Link>
            </div>
            <div className="content">
                <div className="row">
                    <div className="col-10">
                        <a href="#0" className="date">
                            <span className="num">{formatDate().day}</span>
                            <span>{formatDate().month}</span>
                        </a>
                        <div className="tags">
                            <a href="#0">{category}</a>
                        </div>
                        <h4 className="title">
                            <Link href={"/blog/" + slug.current}>
                                <a>{title}</a>
                            </Link>
                        </h4>
                        <p>
                            {excerpt}
                        </p>
                        <Link href={"/blog/" + slug.current}>
                            <a className="simple-btn mt-30">Read More</a>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    )
}